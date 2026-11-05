<?php

declare(strict_types=1);

namespace Core\Utils;

use Core\Traits\Create;

class Paginator implements \JsonSerializable
{
    use Create;


    private array $items = [];

    private int $page = 1;

    private int $perPage = 24;

    private int $total = 0;

    private int $lasPage = 1;

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): Paginator
    {
        $this->items = $items;
        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): Paginator
    {
        $this->page = $page;
        return $this;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): Paginator
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): Paginator
    {
        $this->total = $total;
        return $this;
    }

    public function getLasPage(): int
    {
        return $this->lasPage;
    }

    public function setLasPage(int $lasPage): Paginator
    {
        $this->lasPage = $lasPage;
        return $this;
    }

    /**
     * @return int|null
     */
    public function count(): ?int
    {
        return count($this->items);

    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'page' => $this->page,
            'last_page' => $this->lasPage,
            'per_page' => $this->perPage,
            'total' => $this->total,
            'items' => $this->items
        ];
    }
}