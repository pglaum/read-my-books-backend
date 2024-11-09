<?php

namespace App\Controller;

use App\Entity\BookEvent;
use App\Entity\BookEventType;
use App\Entity\BookList;
use App\Entity\GoogleVolume;
use App\Entity\SavedBook;
use App\Service\GoogleBooks\ApiClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books', name: 'books_')]
class BooksController extends AbstractController
{
    #[Route('/', name: 'add', methods: ['POST'])]
    public function add(
        ApiClient $apiClient,
        #[MapQueryParameter] string $volumeId,
        #[MapQueryParameter] BookList $bookList = BookList::WISHLIST,
        #[MapQueryParameter] bool $isRead = false,
        #[MapQueryParameter] bool $isFavorite = false,
        #[MapQueryParameter] bool $setDate = true,
    ): Response {
        $userId = $this->getUser()->getUserIdentifier();

        /** @var ?SavedBook $book */
        $book = $this->em->getRepository(SavedBook::class)->findOneBy([
            'userId' => $userId,
            'volumeId' => $volumeId,
        ]);

        if (null == $book) {
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
        } else {
            $book
                ->setBookList($bookList)
                ->setFavorite($isFavorite)
            ;

            if (BookList::LIBRARY == $bookList && !$book->isOwned()) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::BOUGHT)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($book)
                ;
                $this->em->persist($event);
            }

            if ($isRead && !$book->isRead()) {
                $event = (new BookEvent())
                    ->setEvent(BookEventType::READ)
                    ->setDate($setDate ? new \DateTime() : null)
                    ->setSavedBook($book)
                ;
                $this->em->persist($event);
            }

            $this->em->persist($book);
            $this->em->flush();
        }

        return new Response(
            $this->serializer->serialize($book),
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json'],
        );
    }
}
