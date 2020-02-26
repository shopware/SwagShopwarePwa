<?php declare(strict_types=1);

namespace SwagVueStorefront\Shopware\Controller\Api;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use SwagVueStorefront\VueStorefront\Bundle\AssetService;
use SwagVueStorefront\VueStorefront\Bundle\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"api"})
 */
class PwaController extends AbstractController
{
    /**
     * @var ConfigurationService
     */
    private $configurationService;

    /**
     * @var AssetService
     */
    private $assetService;

    /**
     * @var Packages
     */
    private $packages;

    public function __construct(ConfigurationService $configurationService, AssetService $assetService, Packages $packages)
    {
        $this->configurationService = $configurationService;
        $this->assetService = $assetService;
        $this->packages = $packages;
    }

    /**
     * @Route("/api/v{version}/_action/pwa/dump-bundles", name="api.action.pwa.dump-bundles", methods={"POST"})
     *
     * @TODO: Resolve the correct asset URL given a LB / Proxy / CDN / static file server using asset management
     *
     * @return JsonResponse
     */
    public function dumpBundles(Request $request): JsonResponse
    {
        try {
            $configArtifact = $this->configurationService->dumpBundles();
            $assetArtifact = $this->assetService->dumpBundles();
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse([
            'success' => true,
            'buildArtifact' => [
                'config' => $this->packages->getUrl($configArtifact),
                'asset' => $this->packages->getUrl($assetArtifact)
            ]
        ]);
    }
}
