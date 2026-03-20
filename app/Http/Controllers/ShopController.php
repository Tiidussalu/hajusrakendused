<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ShopController extends Controller
{
    private function getProducts(): array
    {
        return [
            ['id' => 1,  'name' => 'Tallinna Siluett Poster',  'price' => 14.99, 'image' => 'https://images.unsplash.com/photo-1559329007-40df8a9345d8?w=400', 'description' => 'Ilus Tallinna vanalinna siluett A3 formaadis.', 'category' => 'Postrid'],
            ['id' => 2,  'name' => 'Eesti Kaart Raamiga',      'price' => 29.99, 'image' => 'https://images.unsplash.com/photo-1524661135-423995f22d0b?w=400', 'description' => 'Detailne Eesti kaart puidust raamiga.', 'category' => 'Kaardid'],
            ['id' => 3,  'name' => 'Merevaik Käevõru',         'price' => 24.50, 'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400', 'description' => 'Looduslik Läänemere merevaik hõbedases seades.', 'category' => 'Ehted'],
            ['id' => 4,  'name' => 'Põltsamaa Mesi 500g',      'price' => 8.90,  'image' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=400', 'description' => 'Eesti looduslik õiemesi otse mesitarult.', 'category' => 'Toidukaubad'],
            ['id' => 5,  'name' => 'Kanepi Lõhnajutt',         'price' => 12.00, 'image' => 'https://images.unsplash.com/photo-1602928309055-571ab1b14f24?w=400', 'description' => 'Käsitsi valmistatud looduslik lõhnajutt.', 'category' => 'Kodu'],
            ['id' => 6,  'name' => 'Muhu Kirja Sokid',         'price' => 18.00, 'image' => 'https://images.unsplash.com/photo-1567401893414-76b7b1e5a7a5?w=400', 'description' => 'Villased käsitöösokid traditsioonilise Muhu kirjaga.', 'category' => 'Rõivad'],
            ['id' => 7,  'name' => 'Kask Käsitöö Õlu 6pak',   'price' => 15.60, 'image' => 'https://images.unsplash.com/photo-1608270586620-248524c67de9?w=400', 'description' => 'Eesti käsitööõlu, kase ja humala kooslus.', 'category' => 'Joogid'],
            ['id' => 8,  'name' => 'Sauna Vihakimbu Komplekt', 'price' => 22.00, 'image' => 'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=400', 'description' => 'Kuivatatud kase ja tamme vihtade komplekt saunaks.', 'category' => 'Saun'],
            ['id' => 9,  'name' => 'Härmа Hõbeehte Komplekt',  'price' => 45.00, 'image' => 'https://images.unsplash.com/photo-1573408301185-9519f94f55d3?w=400', 'description' => 'Käsitsi valmistatud hõbeehted Eesti motiividega.', 'category' => 'Ehted'],
            ['id' => 10, 'name' => 'Rukkileib Hapuoblikas',    'price' => 5.50,  'image' => 'https://images.unsplash.com/photo-1586444248902-2f64eddc13df?w=400', 'description' => 'Traditsiooniline Eesti hapuoblikaleib.', 'category' => 'Toidukaubad'],
            ['id' => 11, 'name' => 'Looduslik Kadaka Seep',    'price' => 9.90,  'image' => 'https://images.unsplash.com/photo-1607006483224-99cd15b3cb67?w=400', 'description' => 'Käsitsi valmistatud kadaka eeterlikku õliga seep.', 'category' => 'Hooldus'],
            ['id' => 12, 'name' => 'Puust Vana-Tallinn Karp',  'price' => 32.00, 'image' => 'https://images.unsplash.com/photo-1531427186611-ecfd6d936c79?w=400', 'description' => 'Nikerdatud puidust karp Tallinna vanalinna kujundustega.', 'category' => 'Suveniirid'],
        ];
    }

    public function index(): Response
    {
        return Inertia::render('Shop/Index', [
            'products'   => $this->getProducts(),
            'stripeKey'  => config('services.stripe.key'),
        ]);
    }

    public function checkout(Request $request): Response
    {
        return Inertia::render('Shop/Checkout', [
            'stripeKey' => config('services.stripe.key'),
        ]);
    }

    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'amount'     => 'required|numeric|min:1',
            'items'      => 'required|array',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email',
            'phone'      => 'nullable|string|max:30',
        ]);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        // Salvesta tellimus andmebaasi
        $order = Order::create([
            'first_name'            => $request->first_name,
            'last_name'             => $request->last_name,
            'email'                 => $request->email,
            'phone'                 => $request->phone,
            'items'                 => $request->items,
            'total'                 => $request->amount / 100,
            'status'                => 'pending',
            'stripe_payment_intent' => null,
        ]);

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount'   => (int) $request->amount,
            'currency' => 'eur',
            'metadata' => [
                'order_id' => $order->id,
                'email'    => $request->email,
            ],
        ]);

        $order->update(['stripe_payment_intent' => $paymentIntent->id]);

        return response()->json([
            'clientSecret' => $paymentIntent->client_secret,
            'orderId'      => $order->id,
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        $request->validate(['order_id' => 'required|integer']);

        $order = Order::findOrFail($request->order_id);
        $order->update(['status' => 'paid']);

        return Inertia::render('Shop/Success', ['order' => $order]);
    }

    public function stripeWebhook(Request $request)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sig     = $request->header('Stripe-Signature');
        $secret  = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig, $secret);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $pi = $event->data->object;
            Order::where('stripe_payment_intent', $pi->id)->update(['status' => 'paid']);
        }

        if ($event->type === 'payment_intent.payment_failed') {
            $pi = $event->data->object;
            Order::where('stripe_payment_intent', $pi->id)->update(['status' => 'failed']);
        }

        return response()->json(['received' => true]);
    }
}
