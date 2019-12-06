<?php
declare(strict_types=1);

namespace Snowdog\BulletPoints\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Snowdog\BulletPoints\Model\Processor\BulletPoints;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Store\Model\Store;

class BulletPointsCommand extends Command
{
    const ARGUMENT_ATTRIBUTE_IDS = 'attribute-ids';
    const ARGUMENT_CATEGORY_IDS = 'category-ids';
    const ARGUMENT_SKUS = 'skus';

    /**
     * @var BulletPoints
     */
    private $bulletPoints;

    /**
     * @var State
     */
    private $state;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        BulletPoints $bulletPoints,
        State $state,
        StoreManagerInterface $storeManager
    ) {
        $this->bulletPoints = $bulletPoints;
        $this->state = $state;
        $this->storeManager = $storeManager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('snowdog:collect:bullet-points')
            ->setDescription('Collect Bullet Points and set data for "selling_features_bullets" attribute')
            ->setDefinition([
                new InputArgument(
                    self::ARGUMENT_CATEGORY_IDS,
                    InputArgument::REQUIRED,
                    'Category IDs'
                ),
                new InputArgument(
                    self::ARGUMENT_ATTRIBUTE_IDS,
                    InputArgument::REQUIRED,
                    'Sorted Product Attribute IDs'
                ),
                new InputArgument(
                    self::ARGUMENT_SKUS,
                    InputArgument::OPTIONAL,
                    'Comma separated SKU list'
                ),
            ]);

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
        $this->state->setAreaCode(Area::AREA_FRONTEND);

        $categoryIds = $input->getArgument(self::ARGUMENT_CATEGORY_IDS);
        $categoryIdsArray = $this->parseAndFilter($categoryIds);
        $attributeIds = $input->getArgument(self::ARGUMENT_ATTRIBUTE_IDS);
        $attributeIdsArray = $this->parseAndFilter($attributeIds);
        $skus = $input->getArgument(self::ARGUMENT_SKUS);
        $skusArray = explode(',', $skus);
        $skusArray = array_filter($skusArray);

        if (is_null($attributeIds) || count($attributeIdsArray) == 0) {
            throw new \InvalidArgumentException(
                'Argument ' . self::ARGUMENT_ATTRIBUTE_IDS . ' is missing or it is empty.'
            );
        }

        $output->writeln(
            '<info>Attribute IDs: ' . implode(',', $attributeIdsArray) . '</info>'
        );

        if (!is_null($categoryIds) && count($categoryIdsArray) > 0) {
            $output->writeln(
                '<info>Category IDs: ' . implode(',', $categoryIdsArray) . '</info>'
            );
        }

        if (!is_null($skus) && count($skusArray) > 0) {
            $output->writeln(
                '<info>SKUs: ' . implode(',', $skusArray) . '</info>'
            );
        }

        $this->bulletPoints->execute($attributeIdsArray, $categoryIdsArray, $skusArray);
    }

    /**
     * @param null|string $string
     * @return array
     */
    private function parseAndFilter($string): array
    {
        $ids = explode(',', $string);
        $filteredIds = array_filter($ids, 'is_numeric');

        return $filteredIds;
    }
}
