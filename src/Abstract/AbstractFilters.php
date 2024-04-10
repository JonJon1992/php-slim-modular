<?php

namespace Core\Abstract;

use Core\Utils\Serializable;

abstract class AbstractFilters extends Serializable
{
    protected $page;

    protected $per_page;

    protected $columns;

    protected $not_columns;

    protected ?array $with_associations = [];

    protected $order;

    protected $order_direction;

    public function __construct(?int $pageDefault = 1, ?int $perPageDefault = 30)
    {

        $this->page = $pageDefault;
        $this->per_page = $perPageDefault;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function setPage(?int $page): AbstractFilters
    {
        $this->page = $page;
        return $this;
    }

    public function getPerPage(): ?int
    {
        return $this->per_page;
    }

    public function setPerPage(?int $per_page): AbstractFilters
    {
        $this->per_page = $per_page;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getColumns(): mixed
    {
        return $this->columns;
    }

    /**
     * @param mixed $columns
     * @return AbstractFilters
     */
    public function setColumns($columns): AbstractFilters
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNotColumns(): mixed
    {
        return $this->not_columns;
    }

    /**
     * @param mixed $not_columns
     * @return AbstractFilters
     */
    public function setNotColumns($not_columns): AbstractFilters
    {
        $this->not_columns = $not_columns;
        return $this;
    }

    public function getWithAssociations(): array
    {
        return $this->with_associations;
    }

    public function setWithAssociations(?array $with_associations): AbstractFilters
    {
        $this->with_associations = $with_associations ?? [];
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrder(): mixed
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     * @return AbstractFilters
     */
    public function setOrder($order): AbstractFilters
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderDirection(): mixed
    {
        return $this->order_direction;
    }

    /**
     * @param mixed $order_direction
     * @return AbstractFilters
     */
    public function setOrderDirection($order_direction): AbstractFilters
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
