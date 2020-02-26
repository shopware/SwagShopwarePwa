<?php declare(strict_types=1);

namespace SwagVueStorefront\Shopware\Controller\Api;

use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use SwagVueStorefront\VueStorefront\Bundle\AssetService;
use SwagVueStorefront\VueStorefront\Bundle\ConfigurationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

    public function __construct(ConfigurationService $configurationService, AssetService $assetService)
    {
        $this->configurationService = $configurationService;
        $this->assetService = $assetService;
    }

    /**
     * @Route("/api/v{version}/_action/pwa/dump-bundles", name="api.action.pwa.dump-bundles", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function dumpBundles(): JsonResponse
    {
        try {
            $configArtifact = $this->configurationService->dumpBundles();
            $assetArtifact = $this->assetService->dumpBundles();
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }

        return new JsonResponse([
            'success' => 1,
            'buildArtifact' => [
                'config' => $configArtifact,
                'asset' => $assetArtifact
            ]
        ]);
    }
}
