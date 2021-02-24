<?php

namespace App\Entity;

use App\Repository\BookRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookRepository::class)
 */
class Book
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Title cannot be empty.")
     * @Assert\Length(
     * min = 3,
     * max = 255,
     * minMessage = "Book title must be at least {{ limit }} characters long.",
     * maxMessage = "Book title cannot be longer than {{ limit }} characters."
     * )
     * @Assert\Regex(
     * pattern="/[@#$%^<>|~]/",
     * match=false,
     * message="Book title cannot contain symbols @#$%^<>|~."
     * )
     */
    private $title;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank(message="Pages cannot be empty.")
     * @Assert\Positive(message="Pages must be positive number.")
     */
    private $pages;

    /**
     * @ORM\Column(type="string", length=64)
     * @Assert\NotBlank(message="Isbn cannot be empty.")
     * @Assert\Length(
     * min = 3,
     * max = 64,
     * minMessage = "Isbn must be at least {{ limit }} characters long.",
     * maxMessage = "Isbn cannot be longer than {{ limit }} characters."
     * )
     * @Assert\Regex(
     * pattern="/^[a-z\d\-\s]+$/",
     * match=true,
     * message="Isbn can contain letters, digits, spaces and - only."
     * )
     */
    private $isbn;

    /**
     * @ORM\Column(type="text")
     * @Assert\NotBlank(message="Short description cannot be empty.")
     * @Assert\Regex(
     * pattern="/^[#^<>|~]+$/",
     * match=false,
     * message="Short description cannot contain symbols #^<>|~."
     * )
     */
    private $short_description;

    /**
     * @ORM\Column(type="integer")
     */
    private $author_id;

    /**
     * @ORM\ManyToOne(targetEntity=Author::class, inversedBy="books")
     */
    private $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPages(): ?int
    {
        return $this->pages;
    }

    public function setPages(int $pages): self
    {
        $this->pages = $pages;

        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): self
    {
        $this->isbn = $isbn;

        return $this;
    }

    public function getShortDescription(): ?string
    {
        return $this->short_description;
    }

    public function setShortDescription(string $short_description): self
    {
        $this->short_description = $short_description;

        return $this;
    }

    public function getAuthorId(): ?int
    {
        return $this->author_id;
    }

    public function setAuthorId(int $author_id): self
    {
        $this->author_id = $author_id;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
    }
}
