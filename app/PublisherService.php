<?php

namespace App;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class PublisherService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }

    public function storePublish($message)
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'), env('MQ_VHOST'));
        $channel = $connection->channel();
        $channel->exchange_declare('store_exchange', 'direct', false, false, false);
        $channel->queue_declare('store_queue', false, false, false, false);
        $channel->queue_bind('store_queue', 'store_exchange', 'store_exchange');
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, 'store_exchange', 'store_exchange');
        echo " [x] Sent $message to store_exchange / store_queue.\n";
        $channel->close();
        $connection->close();
    }
    public function updatePublish($message)
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'), env('MQ_VHOST'));
        $channel = $connection->channel();
        $channel->exchange_declare('update_exchange', 'direct', false, false, false);
        $channel->queue_declare('update_queue', false, false, false, false);
        $channel->queue_bind('update_queue', 'update_exchange', 'update_exchange');
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, 'update_exchange', 'update_exchange');
        echo " [x] Sent $message to update_exchange / update_queue.\n";
        $channel->close();
        $connection->close();
    }
    public function deletePublish($message)
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'), env('MQ_VHOST'));
        $channel = $connection->channel();
        $channel->exchange_declare('delete_exchange', 'direct', false, false, false);
        $channel->queue_declare('delete_queue', false, false, false, false);
        $channel->queue_bind('delete_queue', 'delete_exchange', 'delete_exchange');
        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, 'delete_exchange', 'delete_exchange');
        echo " [x] Sent $message to delete_exchange / delete_queue.\n";
        $channel->close();
        $connection->close();
    }
}
