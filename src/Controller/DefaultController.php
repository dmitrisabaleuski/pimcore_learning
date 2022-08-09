<?php

namespace App\Controller;

use App\Services\SegmentTrackingHelperService;
use Knp\Component\Pager\PaginatorInterface;
use Pimcore\Bundle\EcommerceFrameworkBundle\Factory;
use Pimcore\Bundle\EcommerceFrameworkBundle\FilterService\ListHelper;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\DemoCategory;
use Pimcore\Twig\Extension\Templating\HeadTitle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Pimcore\Model\DataObject\DemoProduct;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Website\Navigation\BreadcrumbHelperService;
use Pimcore\Bundle\EcommerceFrameworkBundle\IndexService\ProductList\ProductListInterface;
use Pimcore\Model\DataObject\FilterDefinition;
use Pimcore\Config;


class DefaultController extends FrontendController
{
    /**
     * @Template
     *
     * @param Request $request
     *
     * @return array
     */
    public function defaultAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/demoproducts")
     * @return Response
     */
    public function academyAction(Request $request, HeadTitle $headTitleHelper, Factory $ecommerceFactory,  ListHelper $listHelper, PaginatorInterface $paginator) {
        $products = [];
        $productList = Factory::getInstance()->getIndexService()->getProductListForTenant('Academy');
        foreach($productList as $product) {
            $products[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
            ];
        }
        return $this->render('default/data_object.html.twig', ['productListing' => $products]);

    }

    /**
     * @Route("/dataobject")
     * @return Response
     */
    public function dataObjectAction(Request $request) {
        $a = 1;
        ++$a;
        echo $a;
        exit;
        $productListing = new DemoProduct\Listing();
        $productListing->setCondition('price > ?', [intval($request->get('from-price'))]);

        $data = [];
        foreach ($productListing as $product) {

            $data[] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice()
            ];

        }

        return $this->render('default/data_object.html.twig', ['productListing' => $data]);
    }

    /**
     * @Route("/shop/{path}{categoryname}~c{category}", name="products-list", defaults={"path"=""}, requirements={"path"=".*?", "categoryname"="[\w-]+", "category"="\d+"})
     * @param Request $request
     * @param Factory $ecommerceFactory
     * @param PaginatorInterface $paginator
     * @param ListHelper $listHelper
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listingAction(Request $request, Factory $ecommerceFactory, PaginatorInterface $paginator, ListHelper $listHelper)
    {
        $params = array_merge($request->query->all(), $request->attributes->all());

        $params['parentCategoryIds'] = $params['category'] ?? null;

        $category = DemoCategory::getById($params['category'] ?? null);
        $params['category'] = $category;

        $indexService = $ecommerceFactory->getIndexService();
        $productListing = $indexService->getProductListForCurrentTenant();
        //$productListing->setVariantMode(ProductListInterface::VARIANT_MODE_VARIANTS_ONLY);
        $params['productListing'] = $productListing;

        // Current filter loading
        if ($category) {
            $filterDefinition = $category->getFilterDefinition();

            //TODO We can track segments for personalization on this step after
        }

        if ($request->get('filterdefinition') instanceof FilterDefinition) {
            $filterDefinition = $request->get('filterdefinition');
        }

        // It seems that this made for make sure that we have filter definition
        if (empty($filterDefinition)) {
            $filterDefinition = Config::getWebsiteConfig()->get('fallbackFilterdefinition');
        }

        $filterService = $ecommerceFactory->getFilterService();
        $listHelper->setupProductList($filterDefinition, $productListing, $params, $filterService, true);
        $params['filterService'] = $filterService;
        $params['filterDefinition'] = $filterDefinition;

        $paginator = $paginator->paginate(
            $productListing,
            $request->get('page', 1),
            10
        );

        $params['results'] = $paginator;

        return $this->render('default/elasticsearch.html.twig', $params);
    }

}
