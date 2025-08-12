<?php

declare(strict_types=1);

namespace App\RealWorld;

/**
 * Real-World Usage Example - E-commerce Application.
 *
 * This file demonstrates how to integrate the KaririCode Logging Framework
 * into a real-world application. All supporting classes are defined within
 * the 'App\RealWorld' namespace to showcase a clean, organized structure.
 */

// Step 1: Include the Composer autoloader to make all project classes available.
require_once __DIR__ . '/../vendor/autoload.php';

// Step 2: Import necessary classes from the logging library into the current namespace.
use KaririCode\Contract\Logging\Logger;
use KaririCode\Logging\LoggerConfiguration;
use KaririCode\Logging\LoggerFactory;
use KaririCode\Logging\LoggerRegistry;
use KaririCode\Logging\Service\LoggerServiceProvider;
use KaririCode\Logging\Util\Config;

// ============================================================================
// PART 1: DEFINITION OF ALL SUPPORTING CLASSES FOR THE EXAMPLE
// ============================================================================

// --- Entities ---
class Order
{
    public function __construct(
        private string $id,
        private int $customerId,
        private array $items,
        private float $totalAmount,
        private OrderStatus $status
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }
}

class OrderItem
{
    public function __construct(private int $id, private int $quantity)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}

// --- DTOs (Data Transfer Objects) ---
class OrderRequest
{
    public function __construct(
        private int $customerId,
        private array $items,
        private float $totalAmount,
        private string $currency,
        private string $paymentMethod,
        private array $paymentDetails
    ) {
    }

    public function getCustomerId(): int
    {
        return $this->customerId;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getPaymentDetails(): array
    {
        return $this->paymentDetails;
    }
}

class OrderResponse
{
    public function __construct(private Order $order)
    {
    }

    public function toArray(): array
    {
        return [
            'order_id' => $this->order->getId(),
            'status' => $this->order->getStatus()->value,
            'total' => $this->order->getTotalAmount(),
        ];
    }
}

class PaymentResult
{
    public function __construct(
        private bool $successful,
        private ?string $transactionId,
        private ?string $errorCode,
        private ?string $errorMessage
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->successful;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }
}

// --- Enum ---
enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case FAILED = 'failed';
}

// --- Repositories & Integrations ---
class OrderRepository
{
    public function create(array $data): Order
    {
        echo "DATABASE: Creating order {$data['id']}...\n";
        if (rand(1, 100) > 98) { // Simulate a rare database failure
            throw new DatabaseException('Connection timed out');
        }

        return new Order($data['id'], $data['customer_id'], $data['items'], $data['total_amount'], $data['status']);
    }
}

class PaymentGateway
{
    public function processPayment(array $details, float $amount, string $orderId): PaymentResult
    {
        echo "PAYMENT: Processing payment for order {$orderId}...\n";
        if (rand(1, 100) > 95) { // Simulate a gateway error
            throw new PaymentGatewayException('Gateway unavailable');
        }
        if ($amount < 0) { // Simulate a payment failure
            return new PaymentResult(false, null, '1001', 'Invalid amount');
        }

        return new PaymentResult(true, 'TRANS-' . uniqid(), null, null);
    }
}

class NotificationService
{
    public function sendOrderConfirmation(Order $order): void
    {
        echo "NOTIFICATION: Sending confirmation for order {$order->getId()}...\n";
        if (rand(1, 100) > 90) { // Simulate a notification service failure
            throw new NotificationException('SMTP server not responding');
        }
    }
}

// --- Custom Exceptions ---
class OrderProcessingException extends \Exception
{
}
class ValidationException extends \Exception
{
}
class PaymentException extends \Exception
{
}
class PaymentGatewayException extends \Exception
{
}
class OrderCreationException extends \Exception
{
}
class DatabaseException extends \Exception
{
}
class NotificationException extends \Exception
{
}

// --- Other Supporting Classes ---
class JsonResponse
{
    public function __construct(private array $data, private int $status)
    {
    }

    public function __toString(): string
    {
        return json_encode($this->data, JSON_PRETTY_PRINT);
    }
}

class Container
{
    private array $bindings = [];

    public function singleton(string $key, \Closure $resolver): void
    {
        $this->bindings[$key] = $resolver($this);
    }

    public function get(string $key)
    {
        return $this->bindings[$key] ?? null;
    }
}

// --- Main Business Logic Service ---
final class OrderService
{
    private Logger $logger;
    private Logger $queryLogger;
    private Logger $performanceLogger;
    private Logger $errorLogger;

    public function __construct(
        private LoggerRegistry $loggerRegistry,
        private PaymentGateway $paymentGateway,
        private OrderRepository $orderRepository,
        private NotificationService $notificationService
    ) {
        $this->initializeLoggers();
    }

    public function processOrder(OrderRequest $request): OrderResponse
    {
        $startTime = microtime(true);
        $orderId = $this->generateOrderId();

        $this->logger->info('Order processing started', [
            'order_id' => $orderId,
            'customer_id' => $request->getCustomerId(),
            'items_count' => count($request->getItems()),
            'total_amount' => $request->getTotalAmount(),
        ]);

        try {
            $this->validateOrder($request, $orderId);
            $paymentResult = $this->processPayment($request, $orderId);
            $order = $this->createOrder($request, $paymentResult, $orderId);
            $this->sendOrderConfirmation($order);

            $executionTime = (microtime(true) - $startTime) * 1000;
            $this->logOrderSuccess($order, $executionTime);

            return new OrderResponse($order);
        } catch (\Throwable $e) {
            $this->handleOrderFailure($e, $request, $orderId, $startTime);
            throw new OrderProcessingException('Failed to process order: ' . $e->getMessage(), previous: $e);
        }
    }

    private function initializeLoggers(): void
    {
        try {
            $this->logger = $this->loggerRegistry->getLogger('default');
            $this->queryLogger = $this->loggerRegistry->getLogger('query');
            $this->performanceLogger = $this->loggerRegistry->getLogger('performance');
            $this->errorLogger = $this->loggerRegistry->getLogger('error');
        } catch (\Throwable $e) {
            $this->logger = $this->loggerRegistry->getLogger('default');
            $this->queryLogger = $this->logger;
            $this->performanceLogger = $this->logger;
            $this->errorLogger = $this->logger;
            $this->logger->warning('Some specialized loggers not available, using fallback', ['error' => $e->getMessage()]);
        }
    }

    private function validateOrder(OrderRequest $request, string $orderId): void
    {
        $this->logger->debug('Order validation started', ['order_id' => $orderId]);
        if (!$this->isValidCustomer($request->getCustomerId())) {
            $this->logger->warning('Invalid customer attempted order', ['order_id' => $orderId, 'customer_id' => $request->getCustomerId(), 'ip_address' => $this->getClientIp()]);
            throw new ValidationException('Invalid customer');
        }
        foreach ($request->getItems() as $item) {
            if (!$this->isItemAvailable($item)) {
                $this->logger->warning('Unavailable item in order', ['order_id' => $orderId, 'item_id' => $item->getId(), 'requested_quantity' => $item->getQuantity()]);
                throw new ValidationException("Item {$item->getId()} not available");
            }
        }
        $this->logger->debug('Order validation completed successfully', ['order_id' => $orderId]);
    }

    private function processPayment(OrderRequest $request, string $orderId): PaymentResult
    {
        $startTime = microtime(true);
        $this->logger->info('Payment processing started', ['order_id' => $orderId, 'amount' => $request->getTotalAmount(), 'currency' => $request->getCurrency(), 'payment_method' => $request->getPaymentMethod()]);
        try {
            $paymentResult = $this->paymentGateway->processPayment($request->getPaymentDetails(), $request->getTotalAmount(), $orderId);
            $executionTime = (microtime(true) - $startTime) * 1000;
            if ($paymentResult->isSuccessful()) {
                $this->logger->info('Payment processed successfully', ['order_id' => $orderId, 'transaction_id' => $paymentResult->getTransactionId(), 'execution_time_ms' => $executionTime]);
            } else {
                $this->logger->warning('Payment failed', ['order_id' => $orderId, 'error_code' => $paymentResult->getErrorCode(), 'error_message' => $paymentResult->getErrorMessage(), 'execution_time_ms' => $executionTime]);
                throw new PaymentException($paymentResult->getErrorMessage());
            }

            return $paymentResult;
        } catch (PaymentGatewayException $e) {
            $this->errorLogger->error('Payment gateway error', ['order_id' => $orderId, 'gateway' => get_class($this->paymentGateway), 'error' => $e->getMessage(), 'error_code' => $e->getCode()]);
            throw new PaymentException('Payment processing failed', previous: $e);
        }
    }

    private function createOrder(OrderRequest $request, PaymentResult $paymentResult, string $orderId): Order
    {
        $queryStartTime = microtime(true);
        try {
            $order = $this->orderRepository->create(['id' => $orderId, 'customer_id' => $request->getCustomerId(), 'items' => $request->getItems(), 'total_amount' => $request->getTotalAmount(), 'payment_transaction_id' => $paymentResult->getTransactionId(), 'status' => OrderStatus::CONFIRMED, 'created_at' => new \DateTimeImmutable()]);
            $queryTime = (microtime(true) - $queryStartTime) * 1000;
            $this->queryLogger->info('Order record created', ['order_id' => $orderId, 'query_time_ms' => $queryTime, 'table' => 'orders', 'operation' => 'insert']);

            return $order;
        } catch (DatabaseException $e) {
            $this->errorLogger->critical('Failed to create order record', ['order_id' => $orderId, 'error' => $e->getMessage(), 'query_time_ms' => (microtime(true) - $queryStartTime) * 1000]);
            throw new OrderCreationException('Database error during order creation', previous: $e);
        }
    }

    private function sendOrderConfirmation(Order $order): void
    {
        try {
            $this->notificationService->sendOrderConfirmation($order);
            $this->logger->info('Order confirmation sent', ['order_id' => $order->getId(), 'customer_id' => $order->getCustomerId(), 'notification_type' => 'email']);
        } catch (NotificationException $e) {
            $this->logger->warning('Failed to send order confirmation', ['order_id' => $order->getId(), 'error' => $e->getMessage(), 'impact' => 'customer_not_notified']);
        }
    }

    private function logOrderSuccess(Order $order, float $executionTime): void
    {
        $this->logger->info('Order processed successfully', ['order_id' => $order->getId(), 'customer_id' => $order->getCustomerId(), 'total_amount' => $order->getTotalAmount(), 'items_count' => count($order->getItems()), 'status' => $order->getStatus()->value]);
        $this->performanceLogger->info('Order processing performance', ['order_id' => $order->getId(), 'total_execution_time_ms' => $executionTime, 'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2), 'operation' => 'process_order']);
    }

    private function handleOrderFailure(\Throwable $error, OrderRequest $request, string $orderId, float $startTime): void
    {
        $executionTime = (microtime(true) - $startTime) * 1000;
        $this->errorLogger->error('Order processing failed', ['order_id' => $orderId, 'customer_id' => $request->getCustomerId(), 'error_type' => get_class($error), 'error_message' => $error->getMessage(), 'error_file' => $error->getFile(), 'error_line' => $error->getLine(), 'execution_time_ms' => $executionTime, 'memory_usage_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)]);
        if ($error instanceof PaymentException) {
            $this->errorLogger->critical('Payment failure requires attention', ['order_id' => $orderId, 'amount' => $request->getTotalAmount(), 'impact' => 'revenue_loss', 'requires_manual_review' => true]);
        }
    }

    private function generateOrderId(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    private function getClientIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    private function isValidCustomer(int $customerId): bool
    {
        return $customerId > 0;
    }

    private function isItemAvailable(OrderItem $item): bool
    {
        return $item->getQuantity() > 0;
    }
}

class ExampleController
{
    public function __construct(private OrderService $orderService, private LoggerRegistry $loggerRegistry)
    {
    }

    public function createOrder(OrderRequest $request): JsonResponse
    {
        $logger = $this->loggerRegistry->getLogger('default');
        try {
            $logger->info('Order creation request received', ['endpoint' => '/api/orders', 'method' => 'POST', 'customer_id' => $request->getCustomerId(), 'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'CLI']);
            $response = $this->orderService->processOrder($request);

            return new JsonResponse(['success' => true, 'order' => $response->toArray()], 201);
        } catch (OrderProcessingException $e) {
            $logger->error('Order creation failed at controller level', ['endpoint' => '/api/orders', 'error' => $e->getMessage(), 'customer_id' => $request->getCustomerId()]);

            return new JsonResponse(['success' => false, 'error' => 'Order processing failed'], 400);
        }
    }
}

class LoggingServiceProvider
{
    public function register(Container $container): void
    {
        $container->singleton(LoggerRegistry::class, function () {
            $configArray = include config_path('logging.php');
            $loggerConfig = new LoggerConfiguration();
            foreach ($configArray as $key => $value) {
                $loggerConfig->set($key, $value);
            }
            $loggerFactory = new LoggerFactory($loggerConfig);
            $loggerRegistry = new LoggerRegistry();
            $serviceProvider = new LoggerServiceProvider($loggerConfig, $loggerFactory, $loggerRegistry);
            $serviceProvider->register();

            return $loggerRegistry;
        });
    }
}

// ============================================================================
// PART 2: SCRIPT EXECUTION BLOCK
// ============================================================================

// --- Helper Functions ---
if (!function_exists('App\RealWorld\config_path')) {
    function config_path(string $path = ''): string
    {
        return dirname(__DIR__) . '/config/' . $path;
    }
}

// --- Bootstrap ---
Config::loadEnv();

$container = new Container();
$loggingProvider = new LoggingServiceProvider();
$loggingProvider->register($container);

$loggerRegistry = $container->get(LoggerRegistry::class);

// --- Service Instantiation ---
$paymentGateway = new PaymentGateway();
$orderRepository = new OrderRepository();
$notificationService = new NotificationService();

$orderService = new OrderService(
    $loggerRegistry,
    $paymentGateway,
    $orderRepository,
    $notificationService
);

$controller = new ExampleController($orderService, $loggerRegistry);

// --- HTTP Request Simulation ---
echo "========================================\n";
echo "  Simulating a Successful Order Request \n";
echo "========================================\n";

$successfulRequest = new OrderRequest(
    customerId: 123,
    items: [new OrderItem(1, 2), new OrderItem(2, 1)],
    totalAmount: 99.99,
    currency: 'USD',
    paymentMethod: 'credit_card',
    paymentDetails: ['token' => 'tok_validcard']
);

$response = $controller->createOrder($successfulRequest);
echo $response . "\n\n";

echo "========================================\n";
echo "   Simulating a Failed Order Request    \n";
echo "========================================\n";

$failedRequest = new OrderRequest(
    customerId: 456,
    items: [new OrderItem(3, 1)],
    totalAmount: -10.00, // Invalid amount to force a payment failure
    currency: 'USD',
    paymentMethod: 'credit_card',
    paymentDetails: ['token' => 'tok_invalidcard']
);

$response = $controller->createOrder($failedRequest);
echo $response . "\n";
