<?php

namespace Iocaste\Filter;

use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class AbstractFilter
 */
abstract class AbstractFilter implements FilterContract
{
    use Sortable, InteractsWithRequest;

    /**
     * Count of items per page.
     *
     * @var int
     */
    protected const ITEMS_PER_PAGE = 20;

    /**
     * @var BaseRepository
     */
    protected $repository;

    /**
     * @var array
     */
    protected $queryFilters = [];

    /**
     * @var array
     */
    protected $defaultQueryFilters = [];

    /**
     * @var array
     */
    protected $defaultQuerySettings = [
        'paginate' => false,
        'per_page' => self::ITEMS_PER_PAGE,
        'page' => 1,
        'order_by' => 'created_at,desc',
    ];

    /**
     * @param BaseRepository $repository
     *
     * @return $this
     */
    public function setRepository(BaseRepository $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * @param $queryFilters
     *
     * @return $this
     */
    public function setDefaultQueryFilters($queryFilters)
    {
        $this->queryFilters = array_merge($queryFilters, $this->defaultQueryFilters);

        return $this;
    }

    /**
     * @param array $queryFilters
     * @param array $request
     *
     * @return array
     */
    public function getInput(array $queryFilters = [], array $request = []): array
    {
        $defaultQueryKeys = array_keys($this->defaultQuerySettings);

        $input = $this->only(
            array_merge($defaultQueryKeys, $this->getQueryParams($queryFilters)),
            $request
        );

        return array_merge(
            $this->defaultQuerySettings,
            array_filter($input)
        );
    }

    /**
     * @param BaseRepository $repository
     * @param array $input
     *
     * @return BaseRepository
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function pushFilters(BaseRepository $repository, array $input = []): BaseRepository
    {
        foreach ($this->queryFilters as $filter) {
            if (isset($input[$filter['queryParameter']])) {
                $repository = $repository->pushCriteria(
                    new $filter['criteria'](
                        $input[$filter['queryParameter']],
                        $input
                    )
                );
            }
        }

        return $repository;
    }

    /**
     * @param null $limit
     *
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function limit($limit = null)
    {
        $this->repository->pushCriteria(new LimitBy($limit));

        return $this;
    }

    /**
     * @return mixed
     */
    public function all()
    {
        return $this->repository->all();
    }

    /**
     * @param int|null $itemsPerPage
     *
     * @return mixed
     */
    public function paginate(int $itemsPerPage = null)
    {
        if (! $itemsPerPage) {
            $itemsPerPage = self::ITEMS_PER_PAGE;
        }

        return $this->repository->paginate($itemsPerPage);
    }
}
