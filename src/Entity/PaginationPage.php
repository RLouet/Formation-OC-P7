<?php

namespace App\Entity;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Configuration\Annotation as Hateoas;
use Pagerfanta\Pagerfanta;
use OpenApi\Annotations as OA;

/**
 * @Hateoas\Relation(
 *     "first",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters('first'))",
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"products_list"})
 * )
 * @Hateoas\Relation(
 *     "previous",
 *     href = "expr(object.getPreviousPage())",
 *     exclusion = @Hateoas\Exclusion(groups = {"products_list"})
 * )
 * @Hateoas\Relation(
 *     "self",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters())",
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"products_list"})
 * )
 * @Hateoas\Relation(
 *     "next",
 *     href = "expr(object.getNextPage())",
 *     exclusion = @Hateoas\Exclusion(groups = {"products_list"})
 * )
 * @Hateoas\Relation(
 *     "last",
 *     href = @Hateoas\Route(
 *         "expr(object.getRoute())",
 *         parameters = "expr(object.getParameters('last'))",
 *         absolute = true
 *     ),
 *     exclusion = @Hateoas\Exclusion(groups = {"products_list"})
 * )
 */
class PaginationPage
{
    private string $route;
    private array $parameters;
    private Pagerfanta $pager;
    private string $nextPage;
    private string $previousPage;

    /**
     * @OA\Property(default=2)
     */
    #[Serializer\Groups(["products_list"])]
    #[Serializer\Since("1.0")]
    private int $number;

    /**
     * @OA\Property(default=5)
     */
    #[Serializer\Groups(["products_list"])]
    #[Serializer\Since("1.0")]
    private int $items;

    /**
     * @OA\Property(default=20)
     */
    #[Serializer\Groups(["products_list"])]
    #[Serializer\Since("1.0")]
    private int $limit;

    /**
     * @OA\Property(default=2)
     */
    #[Serializer\Groups(["products_list"])]
    #[Serializer\Since("1.0")]
    private int $totalPages;

    /**
     * @OA\Property(default=25)
     */
    #[Serializer\Groups(["products_list"])]
    #[Serializer\Since("1.0")]
    private int $totalItems;

    public function __construct(string $route, array $parameters, Pagerfanta $pager)
    {
        $this->parameters = $parameters;
        $this->route = $route;
        $this->pager = $pager;
        $this->number = $pager->getCurrentPage();
        $this->items = count($pager->getCurrentPageResults());
        $this->limit = $pager->getMaxPerPage();
        $this->totalPages = $pager->getNbPages();
        $this->totalItems = $pager->getNbResults();
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getParameters(?string $page = "current"): array
    {
        $targetParameters = $this->parameters;

        if ($page === "first") {
            $targetParameters["page"] = 1;
        }

        if ($page === "last") {
            $targetParameters["page"] = $this->totalPages;
        }

        return $targetParameters;
    }

    public function setNextPage(string $nextPage): void
    {
        $this->nextPage = $nextPage;
    }

    public function getNextPage(): string
    {
        return $this->nextPage;
    }

    public function setPreviousPage(string $previousPage): void
    {
        $this->previousPage = $previousPage;
    }

    public function getPreviousPage(): string
    {
        return $this->previousPage;
    }
}