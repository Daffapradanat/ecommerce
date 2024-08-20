<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function downloadInvoice(Request $request, $orderId)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $order = Order::findOrFail($orderId);

        if ($request->token !== $this->generateInvoiceToken($order)) {
            return response()->json([
                'error' => 'Invalid token'
            ], 403);
        }

        $pdf = Pdf::loadView('emails.invoice', ['order' => $order]);
        return $pdf->stream('invoice-' . $order->order_id . '.pdf');
    }

    private function generateInvoiceToken($order)
    {
        return hash('sha256', $order->id . $order->order_id . $order->created_at);
    }

    public function getInvoiceLink($orderId)
    {
        $order = Order::findOrFail($orderId);
        $token = $this->generateInvoiceToken($order);

        $invoiceUrl = url('api/invoices/' . $order->id . '?token=' . $token);

        return response()->json([
            'invoice_url' => $invoiceUrl
        ]);
    }
}
