<?php

namespace Core\Abstract;

use Core\Utils\Serializable;

abstract class AbstractFilters extends Serializable
{
    protected int $page;

    protected int $per_page;

    protected ?array $columns;

    protected ?array $not_columns;

    protected ?array $with_associations = [];

    protected ?string $order;

    protected ?string $order_direction;

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     * @return AbstractFilters
     */
    public function setPage(int $page): AbstractFilters
    {
        $this->page = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->per_page;
    }

    /**
     * @param int $per_page
     * @return AbstractFilters
     */
    public function setPerPage(int $per_page): AbstractFilters
    {
        $this->per_page = $per_page;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getNotColumns(): ?array
    {
        return $this->not_columns;
    }

    /**
     * @param array|null $not_columns
     * @return AbstractFilters
     */
    public function setNotColumns(?array $not_columns): AbstractFilters
    {
        $this->not_columns = $not_columns;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getColumns(): ?array
    {
        return $this->columns;
    }

    /**
     * @param array|null $columns
     * @return AbstractFilters
     */
    public function setColumns(?array $columns): AbstractFilters
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getWithAssociations(): ?array
    {
        return $this->with_associations;
    }

    /**
     * @param array|null $with_associations
     * @return AbstractFilters
     */
    public function setWithAssociations(?array $with_associations): AbstractFilters
    {
        $this->with_associations = $with_associations;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }

    /**
     * @param string|null $order
     * @return AbstractFilters
     */
    public function setOrder(?string $order): AbstractFilters
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getOrderDirection(): ?string
    {
        return $this->order_direction;
    }

    /**
     * @param string|null $order_direction
     * @return AbstractFilters
     */
    public function setOrderDirection(?string $order_direction): AbstractFilters
    {
        $this->order_direction = $order_direction;
        return $this;
    }


    /**
     * @param $data
     * @return AbstractFilters
     */
    public function hydrate($data): AbstractFilters
    {
        $data = is_array($data) ? (object)$data : $data;

        return $this->setPage(!empty($data->page) ? (int)$data->page : null)
            ->setPerPage(!empty($data->per_page) ? (int)$data->per_page : null)
            ->setOrder($data->order ?? null)
            ->setOrderDirection($data->direction ?? null)
            ->setColumns($data->propertities ?? null)
            ->setNotColumns($data->not_propertities ?? null)
            ->setWithAssociations($data->with_associations ?? null);

    }

}
