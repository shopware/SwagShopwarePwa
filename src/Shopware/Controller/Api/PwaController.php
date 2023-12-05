<?php declare(strict_types=1);

namespace SwagShopwarePwa\Shopware\Controller\Api;

use SwagShopwarePwa\Pwa\Bundle\AssetService;
use SwagShopwarePwa\Pwa\Bundle\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(defaults={"_routeScope"={"api"}})
 */
class PwaController extends AbstractController
{
    public function __construct(
        private readonly ConfigurationService $configurationService,
        private readonly AssetService $assetService
    ) {
    }

    /**
     * @Route("/api/_action/pwa/dump-bundles", name="api.action.pwa.dump-bundles", methods={"POST"})
     *
     * @TODO: Resolve the correct asset URL given a LB / Proxy / CDN / static file server using asset management
     *
     * @return JsonResponse
     */
    public function dumpBundles(Request $request): JsonResponse
    {
        try {
            $bundleConfig = $this->configurationService->getBundleConfig();
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
                'asset' => DIRECTORY_SEPARATOR . $assetArtifact,
            ],
            'bundleConfig' => $bundleConfig
        ]);
    }
}
