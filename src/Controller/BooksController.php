<?php

namespace App\Controller;

use App\Entity\BookEvent;
use App\Entity\BookEventType;
use App\Entity\BookList;
use App\Entity\GoogleVolume;
use App\Entity\SavedBook;
use App\Security\Voter\BooksVoter;
use App\Service\GoogleBooks\ApiClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/books', name: 'books_')]
class BooksController extends AbstractController
{
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
            $googleVolume = new GoogleVolume($userId, $googleBookJson);
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

    #[Route('/', name: 'delete', methods: ['DELETE'])]
    #[IsGranted(BooksVoter::DELETE)]
    public function delete(
        ApiClient $apiClient,
        #[MapQueryParameter] ?int $id = null,
        #[MapQueryParameter] ?string $volumeId = null,
    ): Response {
        if (null == $id && null == $volumeId) {
            return new Response(null, Response::HTTP_BAD_REQUEST);
        }

        if (null != $id) {
            $savedBook = $this->em->getRepository(SavedBook::class)->find($id);
            if (!$savedBook) {
                return new Response(null, Response::HTTP_NOT_FOUND);
            }

            $this->em->remove($savedBook);
            $this->em->flush();

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        // DELETE by $volumeId
        $googleBook = $this->em->getRepository(GoogleVolume::class)->findOneBy([
            'userId' => $this->getUser()->getUserIdentifier(),
            'volumeId' => $volumeId,
        ]);
        if (!$googleBook) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $savedBook = $this->em->getRepository(SavedBook::class)->findOneBy([
            'userId' => $this->getUser()->getUserIdentifier(),
            'volume' => $googleBook,
        ]);
        if (!$savedBook) {
            return new Response(null, Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($savedBook);
        $this->em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
