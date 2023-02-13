<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Bundle;

use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Plugin\Event\PluginPostActivateEvent;
use Shopware\Core\Framework\Plugin\Event\PluginPostDeactivateEvent;
use Shopware\Core\Framework\Plugin\PluginCollection;
use Shopware\Core\Framework\Plugin\PluginEntity;
use Shopware\Core\Kernel;
use SplFileInfo;
use SwagShopwarePwa\Pwa\Bundle\Helper\FormattingHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AssetService implements EventSubscriberInterface
{
    /**
     * @var string
     */
    private $assetArtifactDirectory = 'pwa-bundles-assets';

    /**
     * @var string
     */
    private $resourcesDirectory = '/Resources/app/pwa';

    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var EntityRepository
     */

    private $pluginRepository;

    /**
     * @var FormattingHelper
     */
    private $helper;

    /**
     * @var FilesystemOperator
     */
    private $fileSystem;

    public function __construct(Kernel $kernel, EntityRepository $pluginRepository, FormattingHelper $helper, FilesystemOperator $fileSystem)
    {
        $this->kernel = $kernel;
        $this->pluginRepository = $pluginRepository;
        $this->helper = $helper;
        $this->fileSystem = $fileSystem;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
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
        list($bundles, $checksum) = $this->getBundles();

        // Zip directory
        $this->createAssetsArchive($archivePath, $bundles);

        return $this->writeToPublicDirectory($archivePath, $checksum);
    }

    private function createAssetsArchive(string $archivePath, array $bundles): void
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

                $localPath = $this->helper->convertToDashCase($bundle['name']) . '/' . substr($file->getRealPath(), strlen($bundleAssetPath) + 1);
                $zip->addFile($file->getRealPath(), $localPath);
            }
        }

        if($zip->count() <= 0)
        {
            $zip->addFromString('_placeholder_', '');
        }

        $zip->close();
    }

    /**
     * @return array<int|string, mixed>
     */
    private function getBundles(): array
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', true));

        /** @var PluginCollection $plugins */
        $plugins = $this->pluginRepository->search($criteria, Context::createDefaultContext());
        $pluginNames = $plugins->map(function (PluginEntity $plugin) {
            return $plugin->getName();
        });

        $kernelBundles = $this->kernel->getBundles();
        $bundles = [];
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

        $checksum = md5(\json_encode($bundles));

        return [$bundles, $checksum];
    }

    private function writeToPublicDirectory(string $sourceArchive, string $checksum = null): string
    {
        try {
            $this->fileSystem->createDirectory('pwa');

            $output = $checksum ?? 'pwa_assets';

            $outputPath = 'pwa/' . $output  . '.zip';

            $this->fileSystem->delete($outputPath);

            $this->fileSystem->writeStream($outputPath, fopen($sourceArchive, 'r'));
        } catch (FilesystemException $e)
        {
            // Catch gracefully
            $outputPath = '';
        }

        return $outputPath;
    }
}
