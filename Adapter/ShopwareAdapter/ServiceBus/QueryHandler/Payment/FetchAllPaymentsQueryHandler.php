<?php

namespace ShopwareAdapter\ServiceBus\QueryHandler\Payment;

use Exception;
use PlentyConnector\Connector\ServiceBus\Query\Payment\FetchAllPaymentsQuery;
use PlentyConnector\Connector\ServiceBus\Query\QueryInterface;
use PlentyConnector\Connector\ServiceBus\QueryHandler\QueryHandlerInterface;
use PlentyConnector\Console\OutputHandler\OutputHandlerInterface;
use Psr\Log\LoggerInterface;
use ShopwareAdapter\DataProvider\Order\OrderDataProviderInterface;
use ShopwareAdapter\ResponseParser\Payment\PaymentResponseParserInterface;
use ShopwareAdapter\ShopwareAdapter;

/**
 * Class FetchAllPaymentsQueryHandler
 */
class FetchAllPaymentsQueryHandler implements QueryHandlerInterface
{
    /**
     * @var PaymentResponseParserInterface
     */
    private $responseParser;

    /**
     * @var OrderDataProviderInterface
     */
    private $dataProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputHandlerInterface
     */
    private $outputHandler;

    /**
     * FetchAllPaymentsQueryHandler constructor.
     *
     * @param PaymentResponseParserInterface $responseParser
     * @param OrderDataProviderInterface     $dataProvider
     * @param LoggerInterface                $logger
     * @param OutputHandlerInterface         $outputHandler
     */
    public function __construct(
        PaymentResponseParserInterface $responseParser,
        OrderDataProviderInterface $dataProvider,
        LoggerInterface $logger,
        OutputHandlerInterface $outputHandler
    ) {
        $this->responseParser = $responseParser;
        $this->dataProvider = $dataProvider;
        $this->logger = $logger;
        $this->outputHandler = $outputHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(QueryInterface $query)
    {
        return $query instanceof FetchAllPaymentsQuery &&
            $query->getAdapterName() === ShopwareAdapter::NAME;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(QueryInterface $query)
    {
        $elements = $this->dataProvider->getOpenOrders();

        $this->outputHandler->startProgressBar(count($elements));

        $parsedElements = [];
        foreach ($elements as $element) {
            $element = $this->dataProvider->getOrderDetails($element['id']);

            try {
                $result = $this->responseParser->parse($element);
            } catch (Exception $exception) {
                $this->logger->error($exception->getMessage());

                $result = null;
            }

            $this->outputHandler->advanceProgressBar();

            if (empty($result)) {
                continue;
            }

            $parsedElements = array_filter($result);

            foreach ($parsedElements as $parsedElement) {
                $parsedElements[] = $parsedElement;
            }
        }

        $this->outputHandler->finishProgressBar();

        return $parsedElements;
    }
}
