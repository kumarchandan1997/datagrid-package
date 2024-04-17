<?php

namespace Datagrid;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class DataGrid
{
    protected $model;

    protected $columns = [];

    protected $searchColumns = [];

    protected $perPage = 20;

    protected $simpleGridConfig;

    protected $columnsAll;

    protected $Request;

    public $currentPage = 1;

    public function __construct()
    {
        $this->Request = Request::capture();
    }

    public function model($model)
    {
        $this->model = $model;

        return $this;
    }

    public function columns(array $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    public function searchColumns(array $searchColumns)
    {
        $this->searchColumns = $searchColumns;

        return $this;
    }

    public function columnsAll(array $columnsAll)
    {
        $this->columnsAll = $columnsAll;

        return $this;
    }

    public function paginate($perPage)
    {
        $rowsPerPage = request()->input('rows-per-page');
        $this->perPage = $rowsPerPage ? $rowsPerPage : $perPage;

        return $this;
    }

    public function getUrl($type = '')
    {
        $currentUrl = $this->Request->fullUrl();
        $queryString = parse_url($currentUrl, PHP_URL_QUERY); // Get the query string
        parse_str($queryString, $parameters); // Parse the query string into an array
        switch ($type) {
            case 'pagination':
                unset($parameters['page']);
                break;
            case 'rows-per-page':
                unset($parameters['rows-per-page']);
                break;
            case 'order':
                unset($parameters['sort_by']);
                unset($parameters['sort_order']);
                break;
        }
        // Rebuild the URL with modified parameters
        $url = $this->Request->url().$this->processUrlParameters($parameters);

        return $url;
    }

    public function processUrlParameters($parameters)
    {
        $parametersStr = '';
        $parameters['grid'] = 'grid';
        $parametersStr = http_build_query($parameters); // Build query string from parameters

        if (! empty($parametersStr)) {
            $parametersStr = '?'.$parametersStr; // Add '?' if parameters are present
        }

        return $parametersStr;
    }

    public function render()
    {
        $query = $this->model::query();
        if (! empty($this->columns)) {
            $query->select($this->columns);
        }
        if (! empty($this->searchColumns)) {
            $searchValue = request()->input('search');
            if ($searchValue) {
                $query->where(function ($query) use ($searchValue) {
                    foreach ($this->searchColumns as $column) {
                        $query->orWhere($column, 'like', '%'.$searchValue.'%');
                    }
                });
            }
        }

        if (request()->has('sort_by') && request()->has('sort_order')) {
            $query->orderBy(request()->sort_by, request()->sort_order);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        // Paginate the results
        $data = $query->paginate($this->perPage);
        $model = $this->model;
        $columns = $this->columns;
        $searchColumns = $this->searchColumns;
        $columnsAll = $this->columnsAll;
        $rank = $data->firstItem();
        $rowsPerPage = $this->perPage;

        // Get URLs
        $urlPagination = $this->getUrl('pagination');
        $urlOrder = $this->getUrl('order');
        $urlSimpleSearch = $this->getUrl('search');
        $url = $this->getUrl();

        // Compact all required variables for the view
        return View::make('datagrid::table', compact(
            'data',
            'model',
            'columns',
            'searchColumns',
            'columnsAll',
            'rank',
            'rowsPerPage',
            'urlPagination',
            'urlOrder',
            'urlSimpleSearch',
            'url'
        ));
    }
}
