<?php

namespace App\Http\Controllers;

use App\Services\FondaService;
use Illuminate\Support\Facades\Cache;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    protected int $perPage = 5;

    public function __construct(
        protected FondaService $fondaService
    ) {}

    public function handle()
    {
        $update = Telegram::getWebhookUpdate();

        $callback = $update->getCallbackQuery();
        if ($callback) {
            return $this->handleCallback($callback);
        }

        $message = $update->getMessage();
        if ($message) {
            if ($message->getLocation()) {
                return $this->handleLocation($message);
            }
            if ($message->getText()) {
                return $this->handleMessage($message);
            }
        }

        return response()->json(['ok' => true]);
    }

    protected function handleMessage($message)
    {
        $chatId = $message->getChat()->getId();
        $text = trim($message->getText());

        if ($text === '/start') {
            $this->sendMainMenu($chatId);
            return response()->json(['ok' => true]);
        }

        $this->sendMainMenu($chatId);
        return response()->json(['ok' => true]);
    }

    protected function handleLocation($message)
    {
        $chatId = $message->getChat()->getId();
        $location = $message->getLocation();

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => 'Ubicación recibida ✅',
            'reply_markup' => json_encode(['remove_keyboard' => true]),
        ]);

        $pendingMode = Cache::pull("fonda_pending_mode_{$chatId}");

        if ($pendingMode === 'all_ordered') {
            $this->replyAllOrdered($chatId, $location->getLatitude(), $location->getLongitude(), 0);
        } else {
            $this->replyNearby($chatId, $location->getLatitude(), $location->getLongitude(), 0);
        }

        return response()->json(['ok' => true]);
    }

    protected function handleCallback($callback)
    {
        $chatId = $callback->getMessage()->getChat()->getId();
        $data = $callback->getData();

        Telegram::answerCallbackQuery(['callback_query_id' => $callback->getId()]);

        if ($data === 'menu_fondas') {
            $this->sendFondasMenu($chatId);
            return response()->json(['ok' => true]);
        }

        if ($data === 'menu_completos') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "🌭 Completos estará disponible pronto. ¡Vuelve más tarde!",
            ]);
            $this->sendMainMenu($chatId);
            return response()->json(['ok' => true]);
        }

        if ($data === 'back_main') {
            $this->sendMainMenu($chatId);
            return response()->json(['ok' => true]);
        }

        if ($data === 'fondas_nearby') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Comparte tu ubicación para buscar fondas cerca tuyo 📍',
                'reply_markup' => json_encode([
                    'keyboard' => [[['text' => '📍 Enviar mi ubicación', 'request_location' => true]]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
            Cache::put("fonda_pending_mode_{$chatId}", 'nearby', now()->addMinutes(5));
            return response()->json(['ok' => true]);
        }

        if ($data === 'fondas_all_ordered') {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => 'Comparte tu ubicación para ordenar todas las fondas por cercanía 📍',
                'reply_markup' => json_encode([
                    'keyboard' => [[['text' => '📍 Enviar mi ubicación', 'request_location' => true]]],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                ]),
            ]);
            Cache::put("fonda_pending_mode_{$chatId}", 'all_ordered', now()->addMinutes(5));
            return response()->json(['ok' => true]);
        }

        if ($data === 'fondas_all') {
            $this->replyAll($chatId, 0);
            return response()->json(['ok' => true]);
        }

        if ($data === 'page_next' || $data === 'page_prev') {
            $state = Cache::get("fonda_search_{$chatId}");

            if (!$state) {
                Telegram::sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Esa búsqueda ya expiró, intenta de nuevo 🙂',
                ]);
                $this->sendFondasMenu($chatId);
                return response()->json(['ok' => true]);
            }

            $newPage = $data === 'page_next'
                ? $state['page'] + 1
                : max(0, $state['page'] - 1);

            if ($state['mode'] === 'nearby') {
                $this->replyNearby($chatId, $state['lat'], $state['lng'], $newPage);
            } elseif ($state['mode'] === 'all_ordered') {
                $this->replyAllOrdered($chatId, $state['lat'], $state['lng'], $newPage);
            } else {
                $this->replyAll($chatId, $newPage);
            }

            return response()->json(['ok' => true]);
        }

        return response()->json(['ok' => true]);
    }

    protected function sendMainMenu($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "¡Hola! 👋 ¿Qué quieres buscar cerca tuyo?",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '🍽️ Fondas', 'callback_data' => 'menu_fondas']],
                    [['text' => '🌭 Completos', 'callback_data' => 'menu_completos']],
                ],
            ]),
        ]);
    }

    protected function sendFondasMenu($chatId)
    {
        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => "🍽️ Fondas — ¿cómo quieres buscar?",
            'reply_markup' => json_encode([
                'inline_keyboard' => [
                    [['text' => '📍 Cerca de mí', 'callback_data' => 'fondas_nearby']],
                    [['text' => '📍 Ver todas, ordenadas por cercanía', 'callback_data' => 'fondas_all_ordered']],
                    [['text' => '📋 Ver todas (sin ubicación)', 'callback_data' => 'fondas_all']],
                    [['text' => '⬅️ Volver', 'callback_data' => 'back_main']],
                ],
            ]),
        ]);
    }

    protected function replyNearby($chatId, $lat, $lng, $page)
    {
        $radiusMeters = 50000;
        $offset = $page * $this->perPage;

        $results = $this->fondaService->searchNearby($lat, $lng, $radiusMeters, $this->perPage, $offset);
        $total = $this->fondaService->countNearby($lat, $lng, $radiusMeters);

        if ($total === 0) {
            Telegram::sendMessage([
                'chat_id' => $chatId,
                'text' => "No encontramos fondas dentro de 50 km 😕",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [['text' => '📋 Ver todas las fondas', 'callback_data' => 'fondas_all']],
                        [['text' => '⬅️ Volver', 'callback_data' => 'back_main']],
                    ],
                ]),
            ]);
            return;
        }

        Cache::put("fonda_search_{$chatId}", [
            'mode' => 'nearby',
            'lat' => $lat,
            'lng' => $lng,
            'page' => $page,
        ], now()->addMinutes(15));

        $this->sendFondaPage($chatId, $results, $total, $page, true);
    }

    protected function replyAllOrdered($chatId, $lat, $lng, $page)
    {
        $offset = $page * $this->perPage;

        $results = $this->fondaService->allOrderedByDistance($lat, $lng, $this->perPage, $offset);
        $total = $this->fondaService->countAll();

        Cache::put("fonda_search_{$chatId}", [
            'mode' => 'all_ordered',
            'lat' => $lat,
            'lng' => $lng,
            'page' => $page,
        ], now()->addMinutes(15));

        $this->sendFondaPage($chatId, $results, $total, $page, true);
    }

    protected function replyAll($chatId, $page)
    {
        $offset = $page * $this->perPage;

        $results = $this->fondaService->all($this->perPage, $offset);
        $total = $this->fondaService->countAll();

        Cache::put("fonda_search_{$chatId}", [
            'mode' => 'all',
            'lat' => null,
            'lng' => null,
            'page' => $page,
        ], now()->addMinutes(15));

        $this->sendFondaPage($chatId, $results, $total, $page, false);
    }

    protected function sendFondaPage($chatId, $results, $total, $page, $showDistance)
    {
        $offset = $page * $this->perPage;
        $from = $offset + 1;
        $to = $offset + $results->count();

        $reply = "Mostrando {$from}-{$to} de {$total} fonda(s):\n\n";

        foreach ($results as $f) {
            $reply .= "🎪 *{$f->name}*\n";
            if (!empty($f->address)) {
                $reply .= "📍 {$f->address}\n";
            }
            if (!empty($f->dates)) {
                $reply .= "🗓️ {$f->dates}\n";
            }
            if (!empty($f->artists)) {
                $reply .= "🎤 {$f->artists}\n";
            }
            if (!empty($f->prices)) {
                $reply .= "💰 {$f->prices}\n";
            }
            if ($showDistance && isset($f->distance_m)) {
                $km = round($f->distance_m / 1000, 1);
                $reply .= "📏 {$km} km\n";
            }
            if (!empty($f->lat) && !empty($f->lng)) {
                $mapsLink = "https://www.google.com/maps?q={$f->lat},{$f->lng}";
                $reply .= "[Ver en Google Maps]({$mapsLink})\n";
            }
            $reply .= "\n";
        }

        $navRow = [];
        if ($page > 0) {
            $navRow[] = ['text' => '◀️ Anterior', 'callback_data' => 'page_prev'];
        }
        if ($to < $total) {
            $navRow[] = ['text' => 'Siguiente ▶️', 'callback_data' => 'page_next'];
        }

        $buttons = [];
        if (!empty($navRow)) {
            $buttons[] = $navRow;
        }
        $buttons[] = [['text' => '⬅️ Menú fondas', 'callback_data' => 'menu_fondas']];

        Telegram::sendMessage([
            'chat_id' => $chatId,
            'text' => $reply,
            'parse_mode' => 'Markdown',
            'reply_markup' => json_encode(['inline_keyboard' => $buttons]),
        ]);
    }
}