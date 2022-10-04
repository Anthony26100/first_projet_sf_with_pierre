<?php

namespace App\Data;

/**
 * Class for search posts.
 */
class SearchData
{
    /**
     * The content of the query for title posts.
     *
     * @var string|null
     */
    private ?string $query = '';

    /**
     * Array of tag for the search posts.
     *
     * @var array|null
     */
    private ?array $categories = [];

    /**
     * Array of author for search posts.
     *
     * @var array|null
     */
    private ?array $auteur = [];

    /**
     * Array of active for search posts.
     *
     * @var array|null
     */
    private ?array $active = [];

    /**
     * The number of the page of search.
     *
     * @var array|null
     */
    private ?int $page = 1;

    /**
     * Get array of author for search posts.
     *
     * @return array|null
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set array of author for search posts.
     *
     * @param array|null $auteur Array of author for search posts
     *
     * @return self
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get array of tag for the search posts.
     *
     * @return array|null
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Set array of tag for the search posts.
     *
     * @param array|null $categories Array of tag for the search posts
     *
     * @return self
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Get the content of the query for title posts.
     *
     * @return string|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the content of the query for title posts.
     *
     * @param string|null $query The content of the query for title posts
     *
     * @return self
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get the number of the page of search.
     *
     * @return array|int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set the number of the page of search.
     *
     * @param array|int $page The number of the page of search
     *
     * @return self
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get the value of active.
     *
     * @return ?array
     */
    public function getActive(): ?array
    {
        return $this->active;
    }

    /**
     * Set the value of active.
     *
     * @param ?array $active
     *
     * @return self
     */
    public function setActive(?array $active): self
    {
        $this->active = $active;

        return $this;
    }
}
