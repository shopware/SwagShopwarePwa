<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Bundle\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use SwagShopwarePwa\Pwa\Bundle\AssetService;
use SwagShopwarePwa\Pwa\Bundle\ConfigurationService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpPluginConfigurationCommand extends Command
{
    protected static $defaultName = 'pwa:dump-plugins';

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

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Dump PWA plugin configurations and assets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new ShopwareStyle($input, $output);

        $io->title('Shopware PWA Extension');

        $io->text('Configurations');
        $configArtifact = $this->configurationService->dumpBundles();
        $io->comment('Wrote configurations to \'' . $configArtifact . '\'');

        $io->text('Assets');
        $assetArtifact = $this->assetService->dumpBundles();
        $io->comment('Wrote assets to \'' . $assetArtifact . '\'');

        return self::SUCCESS;
    }
}
