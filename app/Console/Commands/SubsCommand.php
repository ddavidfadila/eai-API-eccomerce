<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class SubsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:subs-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'), env('MQ_VHOST'));
        $channel = $connection->channel();
        $storeCallback = function ($msg) {
            $data = json_decode($msg->body, true);
            $id = $data['id'];

            echo ' [x] Berhasil menambahkan product dengan id: ', $id, "\n";
        };
        $updateCallBack  = function ($msg) {
            $data = json_decode($msg->body, true);
            $id = $data['id'];

            echo ' [x] Berhasil mengubah product dengan id: ', $id, "\n";
        };
        $deleteCallBack  = function ($msg) {
            $data = json_decode($msg->body, true);
            $id = $data['id'];

            echo ' [x] Berhasil delete product dengan id: ', $id, "\n";
        };
        $buyCallBack  = function ($msg) {
            $data = json_decode($msg->body, true);
            $userId = $data['userId'];
            $productId = $data['productId'];
            $cart = Cart::where('userId', $userId)->where('productId', $productId)->first();
            if($cart){
                $cart->update([
                    "qty" => $cart->qty += 1,
                    "totalPrice" => $cart->totalPrice += $data['totalPrice'],
                ]);
                echo ' [x] Berhasil menambahkan quantity product pada cart dengan id: ', $cart->id, "\n";
            }else{
                $cart = Cart::create([
                    "userId" => $data["userId"],
                    "productId" => $data["productId"],
                    "qty" => $data["qty"],
                    "totalPrice" => $data["totalPrice"],
                ]);

                echo ' [x] Berhasil menambah product kedalam cart dengan id: ', $cart->id, "\n";
            }
        };
        $deleteCartCallBack  = function ($msg) {
            $data = json_decode($msg->body, true);
            $id = $data['id'];
            Cart::find($id)->delete();
            echo ' [x] Berhasil menghapus cart dengan id: ', $id, "\n";
        };
        $channel->queue_declare('store_queue', false, false, false, false);
        $channel->basic_consume('store_queue', '', false, true, false, false, $storeCallback);

        $channel->queue_declare('update_queue', false, false, false, false);
        $channel->basic_consume('update_queue', '', false, true, false, false, $updateCallBack);

        $channel->queue_declare('delete_queue', false, false, false, false);
        $channel->basic_consume('delete_queue', '', false, true, false, false, $deleteCallBack);

        $channel->queue_declare('buy_queue', false, false, false, false);
        $channel->basic_consume('buy_queue', '', false, true, false, false, $buyCallBack);

        $channel->queue_declare('delete_cart_queue', false, false, false, false);
        $channel->basic_consume('delete_cart_queue', '', false, true, false, false, $deleteCartCallBack);
        echo 'Waiting for new message', " \n";
        while ($channel->is_consuming()) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}
