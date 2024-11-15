<?php

namespace App\Controller;

use App\Entity\BookEvent;
use App\Entity\BookEventType;
use App\Entity\BookList;
use App\Entity\BookStatusType;
use App\Entity\GoogleVolume;
use App\Entity\SavedBook;
use App\Security\Voter\BooksVoter;
use App\Service\GoogleBooks\ApiClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/books', name: 'books_')]
class BooksController extends AbstractController
{
    #[Route('/home', name: 'homepage', methods: ['GET'])]
    #[IsGranted(BooksVoter::LIST)]
    public function homepage(): Response
    {
        $currentlyReading = $this->em->getRepository(SavedBook::class)->findBy([
            'userId' => $this->getUser()->getUserIdentifier(),
            'bookStatus' => BookStatusType::READING,
        ], [
            'updatedAt' => 'DESC',
        ]);

        $wishlist = $this->em->getRepository(SavedBook::class)->findBy([
            'userId' => $this->getUser()->getUserIdentifier(),
            'bookList' => BookList::WISHLIST,
        ], [
            'updatedAt' => 'DESC',
        ], 5);

        $library = $this->em->getRepository(SavedBook::class)->findBy([
            'userId' => $this->getUser()->getUserIdentifier(),
            'bookList' => BookList::LIBRARY,
        ], [
            'updatedAt' => 'DESC',
        ], 5);

        return new Response(
            $this->serializer->serialize([
                'currentlyReading' => $currentlyReading,
                'wishlist' => $wishlist,
                'library' => $library,
            ]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    #[Route('/', name: 'list', methods: ['GET'])]
    #[IsGranted(BooksVoter::LIST)]
    public function list(
        #[MapQueryParameter] BookList $bookList,
    ): Response {
        $books = $this->em->getRepository(SavedBook::class)->findBy([
            'userId' => $this->getUser()->getUserIdentifier(),
            'bookList' => $bookList,
        ], [
            'updatedAt' => 'DESC',
        ]);

        return new Response(
            $this->serializer->serialize($books),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    #[Route('/', name: 'add', methods: ['POST'])]
    #[IsGranted(BooksVoter::CREATE)]
    public function add(
        ApiClient $apiClient,
        #[MapQueryParameter] string $volumeId,
        #[MapQueryParameter] BookList $bookList = BookList::WISHLIST,
        #[MapQueryParameter] bool $isRead = false,
        #[MapQueryParameter] bool $isFavorite = false,
        #[MapQueryParameter] bool $setDate = true,
    ): Response {
        $userId = $this->getUser()->getUserIdentifier();

        $googleVolume = $this->em->getRepository(GoogleVolume::class)->findOneBy([
            'userId' => $userId,
            'volumeId' => $volumeId,
        ]);
        if (null == $googleVolume) {
            $googleBookJson = $apiClient->get($volumeId);
            $googleVolume = new GoogleVolume($userId, $googleBookJson->getData());
            $this->em->persist($googleVolume);
            $this->em->flush();
        }

        /** @var ?SavedBook $book */
        $book = $this->em->getRepository(SavedBook::class)->findOneBy([
            'userId' => $userId,
            'volume' => $googleVolume,
        ]);

        if (null == $book) {
            $book = (new SavedBook())
                ->setUserId($userId)
                ->setVolume($googleVolume)
                ->setBookList($bookList)
                ->setFavorite($isFavorite)
            ;

            if (BookList::LIBRARY == $bookList) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::BOUGHT)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($book)
                ;
                $this->em->persist($event);
            }

            if ($isRead) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::READ)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($book)
                ;
                $this->em->persist($event);
            }

            $this->em->persist($book);
            $this->em->flush();

            return new Response(
                $this->serializer->serialize($book),
                Response::HTTP_CREATED,
                ['Content-Type' => 'application/json'],
            );
        } else {
            $book
                ->setBookList($bookList)
                ->setFavorite($isFavorite)
            ;

            if (BookList::LIBRARY == $bookList && !$book->isOwned()) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::BOUGHT)
                    ->setDate($setDate ? new \DateTime() : null)
                ;
                $this->em->persist($event);

                $book->addEvent($event);
            }

            if ($isRead && !$book->isRead()) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::READ)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($book)
                ;
                $this->em->persist($event);

                $book->addEvent($event);
            }

            $this->em->persist($book);
            $this->em->flush();

            return new Response(
                $this->serializer->serialize($book),
                Response::HTTP_OK,
                ['Content-Type' => 'application/json'],
            );
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    #[IsGranted(BooksVoter::VIEW, subject: 'savedBook')]
    public function get(
        SavedBook $savedBook,
    ): Response {
        return new Response(
            $this->serializer->serialize($savedBook),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(BooksVoter::DELETE, subject: 'savedBook')]
    public function delete(
        ApiClient $apiClient,
        SavedBook $savedBook,
        #[MapQueryParameter] ?string $volumeId = null,
    ): Response {
        $this->em->remove($savedBook);
        $this->em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);

        // TODO: DELETE by $volumeId
        //        $googleBook = $this->em->getRepository(GoogleVolume::class)->findOneBy([
        //            'userId' => $this->getUser()->getUserIdentifier(),
        //            'volumeId' => $volumeId,
        //        ]);
        //        if (!$googleBook) {
        //            return new Response(null, Response::HTTP_NOT_FOUND);
        //        }
        //
        //        $savedBook = $this->em->getRepository(SavedBook::class)->findOneBy([
        //            'userId' => $this->getUser()->getUserIdentifier(),
        //            'volume' => $googleBook,
        //        ]);
        //        if (!$savedBook) {
        //            return new Response(null, Response::HTTP_NOT_FOUND);
        //        }
        //
        //        $this->em->remove($savedBook);
        //        $this->em->flush();
        //
        //        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}', name: 'patch', methods: ['PATCH'])]
    #[IsGranted(BooksVoter::EDIT, subject: 'savedBook')]
    public function patch(
        SavedBook $savedBook,
        Request $request,
        #[MapQueryParameter] bool $setDate = true,
        #[MapQueryParameter] bool $isRead = false,
    ): Response {
        $submittedData = json_decode($request->getContent(), true) ?? [];

        try {
            $savedBook->updateFromArray($submittedData);
            $this->em->persist($savedBook);

            if (isset($submittedData['bookList']) && BookList::LIBRARY == BookList::tryFrom($submittedData['bookList'])) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::BOUGHT)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($savedBook)
                ;
                $this->em->persist($event);
            }

            if ($isRead && !$savedBook->isRead()) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::READ)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($savedBook)
                ;
                $this->em->persist($event);
            }

            $this->em->flush();
        } catch (\Exception $e) {
            return new Response(
                $this->serializer->serialize(['error' => $e->getMessage()]),
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json'],
            );
        }

        return new Response(
            $this->serializer->serialize($savedBook),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    #[Route('/events/{id}', name: 'event_patch', methods: ['PATCH'])]
    #[IsGranted(BooksVoter::EDIT, subject: 'bookEvent')]
    public function event_patch(
        BookEvent $bookEvent,
        #[MapQueryParameter] ?\DateTime $date,
    ): Response {
        $bookEvent->setDate($date);
        $this->em->persist($bookEvent);
        $this->em->flush();

        return new Response(
            $this->serializer->serialize($bookEvent),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    #[Route('/events/{id}', name: 'event_delete', methods: ['DELETE'])]
    #[IsGranted(BooksVoter::EDIT, subject: 'bookEvent')]
    public function event_delete(
        BookEvent $bookEvent,
    ): Response {
        $this->em->remove($bookEvent);
        $this->em->flush();

        return new Response(
            null,
            Response::HTTP_NO_CONTENT,
        );
    }
}
