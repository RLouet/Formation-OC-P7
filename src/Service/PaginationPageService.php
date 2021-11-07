<?php

namespace App\Service;


use App\Entity\PaginationPage;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaginationPageService
{
    private UrlGeneratorInterface $urlGenerator;
    private SerializerInterface $serializer;
    private string $route;
    private array $parameters;
    private Pagerfanta $pager;

    public function __construct(UrlGeneratorInterface $urlGenerator, SerializerInterface $serializer)
    {
        $this->urlGenerator = $urlGenerator;
        $this->serializer = $serializer;
    }

    public function generatePage(string $route, array $parameters, Pagerfanta $pager, string $type): string
    {
        $this->parameters = $parameters;
        $this->pager = $pager;
        $this->route = $route;
        $page = new PaginationPage($route, $parameters, $pager);
        $page->setPreviousPage($this->generateUrl("previous"));
        $page->setNextPage($this->generateUrl("next"));

        $data = $pager->getCurrentPageResults();

        $result = [
            "_page" => $page,
            $type => $data
        ];

        return $this->serializer->serialize(
            $result,
            'json',
            SerializationContext::create()->setGroups([$type . "_list"])
        );
    }

    private function generateUrl(?string $page): string
    {
        $link = "Unavailable";
        $targetParameters = $this->parameters;

        if ($page === "previous") {
            try {
                $targetParameters["page"] = $this->pager->getPreviousPage();
                $link = $this->urlGenerator->generate($this->route, $targetParameters, UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (\Exception) {
            }
        }

        if ($page === "next") {
            try {
                $targetParameters["page"] = $this->pager->getNextPage();
                $link = $this->urlGenerator->generate($this->route, $targetParameters, UrlGeneratorInterface::ABSOLUTE_URL);
            } catch (\Exception) {
            }
        }

        return $link;
    }
}