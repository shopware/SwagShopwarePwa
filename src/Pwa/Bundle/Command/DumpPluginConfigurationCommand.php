<?php declare(strict_types=1);

namespace SwagShopwarePwa\Pwa\Bundle\Command;

use Shopware\Core\Framework\Adapter\Console\ShopwareStyle;
use SwagShopwarePwa\Pwa\Bundle\AssetService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'pwa:dump-plugins', description: 'Dump PWA plugin configurations and assets')]
class DumpPluginConfigurationCommand extends Command
{
    public function __construct(
        private readonly AssetService $assetService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new ShopwareStyle($input, $output);

        $io->title('Shopware PWA Extension');

        $io->text('Assets');
        $assetArtifact = $this->assetService->dumpBundles();
        $io->comment('Wrote assets to \'' . $assetArtifact . '\'');

        return 0;
    }
}
