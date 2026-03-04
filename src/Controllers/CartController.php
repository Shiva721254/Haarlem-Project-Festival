<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Framework\Csrf;
use App\Framework\Flash;
use App\Framework\Response;
use App\Framework\SessionManager;
use App\Repositories\TicketRepository;
use App\Services\CartService;

final class CartController
{
    /**
     * @return array{0:array<int, array<string, mixed>>, 1:float}
     */
    private function buildCartSummary(): array
    {
        $cartItems = CartService::items();
        $ticketRepo = new TicketRepository();
        $ticketsById = $ticketRepo->findByIds(array_keys($cartItems));

        $lines = [];
        $total = 0.0;

        foreach ($cartItems as $ticketId => $quantity) {
            if (!isset($ticketsById[$ticketId])) {
                continue;
            }

            $ticket = $ticketsById[$ticketId];
            $price = (float)$ticket['price'];
            $lineTotal = $price * $quantity;
            $total += $lineTotal;

            $lines[] = [
                'ticket_id' => $ticketId,
                'event_title' => (string)$ticket['event_title'],
                'ticket_type' => (string)$ticket['ticket_type'],
                'event_date' => (string)$ticket['event_date'],
                'quantity' => $quantity,
                'price' => $price,
                'line_total' => $lineTotal,
            ];
        }

        return [$lines, $total];
    }

    public function show(): Response
    {
        SessionManager::start();

        [$lines, $total] = $this->buildCartSummary();

        return Response::html(view('cart/index', [
            'lines' => $lines,
            'total' => $total,
        ]));
    }

    public function add(): Response
    {
        SessionManager::start();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/cart');
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($ticketId <= 0 || $quantity <= 0) {
            Flash::set('error', 'Invalid ticket selection.');
            return Response::redirect('/schedule');
        }

        $ticketRepo = new TicketRepository();
        $ticket = $ticketRepo->findById($ticketId);
        if ($ticket === null) {
            Flash::set('error', 'Selected ticket not found.');
            return Response::redirect('/schedule');
        }

        $available = (int)$ticket['quantity_available'] - (int)$ticket['quantity_sold'];
        $existing = CartService::items()[$ticketId] ?? 0;
        if (($existing + $quantity) > $available) {
            Flash::set('error', 'Not enough tickets available for that quantity.');
            return Response::redirect('/schedule');
        }

        CartService::add($ticketId, $quantity);
        Flash::set('success', 'Ticket added to cart.');

        return Response::redirect('/cart');
    }

    public function update(): Response
    {
        SessionManager::start();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/cart');
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($ticketId <= 0) {
            Flash::set('error', 'Invalid cart item.');
            return Response::redirect('/cart');
        }

        if ($quantity <= 0) {
            CartService::remove($ticketId);
            Flash::set('success', 'Item removed from cart.');
            return Response::redirect('/cart');
        }

        $ticketRepo = new TicketRepository();
        $ticket = $ticketRepo->findById($ticketId);
        if ($ticket === null) {
            Flash::set('error', 'Selected ticket not found.');
            return Response::redirect('/cart');
        }

        $available = (int)$ticket['quantity_available'] - (int)$ticket['quantity_sold'];
        if ($quantity > $available) {
            Flash::set('error', 'Not enough tickets available for that quantity.');
            return Response::redirect('/cart');
        }

        CartService::update($ticketId, $quantity);
        Flash::set('success', 'Cart item updated.');

        return Response::redirect('/cart');
    }

    public function remove(): Response
    {
        SessionManager::start();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/cart');
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        CartService::remove($ticketId);
        Flash::set('success', 'Item removed from cart.');

        return Response::redirect('/cart');
    }

    public function clear(): Response
    {
        SessionManager::start();

        if (!Csrf::verifyFromPost()) {
            Flash::set('error', 'Invalid CSRF token. Please try again.');
            return Response::redirect('/cart');
        }

        CartService::clear();
        Flash::set('success', 'Cart cleared.');

        return Response::redirect('/cart');
    }

    public function checkout(): Response
    {
        SessionManager::start();

        [$lines, $total] = $this->buildCartSummary();
        if ($lines === []) {
            Flash::set('error', 'Your cart is empty.');
            return Response::redirect('/cart');
        }

        return Response::html(view('checkout/index', [
            'lines' => $lines,
            'total' => $total,
        ]));
    }
}
