<?php declare(strict_types=1);

namespace SwagVueStorefront\VueStorefront\Bundle;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use Shopware\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Kernel;
use SplFileInfo;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

class AssetService implements EventSubscriberInterface
{
    private $assetArtifactDirectory = 'pwa-bundles-assets';

    private $resourcesDirectory = '/Resources/app/pwa';

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var EntityRepositoryInterface
     */

    private $pluginRepository;

    public function __construct(Kernel $kernel, EntityRepositoryInterface $pluginRepository)
    {
        $this->kernel = $kernel;
        $this->pluginRepository = $pluginRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginPostActivateEvent::class => 'dumpBundles',
            PluginPostDeactivateEvent::class => 'dumpBundles'
        ];
    }

    public function dumpBundles(): string
    {
        // Create temporary directory
        $archivePath = $this->kernel->getCacheDir() . '/../../' . $this->assetArtifactDirectory . '.zip';

        // Look for assets
        $bundles = $this->getBundles();

        // Zip directory
        $this->createAssetsArchive($archivePath, $bundles);

        return $this->assetArtifactDirectory . '.zip';
    }

    private function createAssetsArchive(string $archivePath, array $bundles)
    {
        $zip = new \ZipArchive();
        $zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach($bundles as $bundle)
        {
            $bundleAssetPath = $bundle['path'] . $this->resourcesDirectory;

            if(!is_dir($bundleAssetPath))
            {
                continue;
            }

            /** @var SplFileInfo[] $files */
            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($bundleAssetPath));

            foreach($files as $name => $file)
            {

                if(is_dir($name))
                {
                    continue;
                }

                $localPath = $bundle['name'] . '/' . substr($file->getRealPath(), strlen($bundleAssetPath) + 1);
                $zip->addFile($file->getRealPath(), $localPath);
            }
        }

        $zip->close();
    }

    private function getBundles(): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepository->search($criteria, Context::createDefaultContext());
        $pluginNames = $plugins->map(function (PluginEntity $plugin) {
            return $plugin->getName();
        });

        /** @var BundleInterface[] $kernelBundles */
        $kernelBundles = $this->kernel->getBundles();

        foreach ($kernelBundles as $kernelBundle)
        {
            if(!in_array($kernelBundle->getName(), $pluginNames)) {
                continue;
            }

            $bundles[] = [
                'name' => $kernelBundle->getName(),
                'path' => $kernelBundle->getPath()
            ];
        }

        return $bundles;
    }
}
