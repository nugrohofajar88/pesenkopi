<?php

namespace App\Support;

/**
 * Keranjang tamu, session-based (situs ini tanpa login/akun sama sekali).
 * Harga/nama di-resolve ulang dari menu live BE tiap kali ditampilkan (bukan
 * snapshot saat add-to-cart) - pola sama dgn App\Support\GuestCart di larashop-fe.
 */
class GuestCart
{
    private const SESSION_KEY = 'guest_cart';

    public function __construct(private readonly PosApi $api)
    {
    }

    public function add(int $productId, int $qty, array $modifierOptionIds = [], ?string $note = null): void
    {
        $items = $this->rawItems();
        $nextId = $items === [] ? 1 : max(array_column($items, 'cartId')) + 1;

        $items[] = [
            'cartId' => $nextId,
            'productId' => $productId,
            'qty' => max(1, $qty),
            'note' => $note,
            'modifierOptionIds' => array_values(array_unique(array_map('intval', $modifierOptionIds))),
        ];

        $this->save($items);
    }

    public function updateQty(int $cartId, int $qty): void
    {
        $items = $this->rawItems();

        foreach ($items as $i => $item) {
            if ($item['cartId'] === $cartId) {
                if ($qty <= 0) {
                    unset($items[$i]);
                } else {
                    $items[$i]['qty'] = $qty;
                }
                break;
            }
        }

        $this->save(array_values($items));
    }

    public function remove(int $cartId): void
    {
        $items = array_values(array_filter($this->rawItems(), fn (array $i) => $i['cartId'] !== $cartId));
        $this->save($items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return $this->rawItems() === [];
    }

    public function count(): int
    {
        return array_sum(array_column($this->rawItems(), 'qty'));
    }

    /**
     * Resolve tiap baris ke data menu TERBARU dari BE (nama/harga/modifier
     * selalu up-to-date, bukan snapshot lama). Baris yg produk/opsinya sudah
     * tidak ada lagi ditandai unavailable=true, bukan dibuang diam-diam.
     */
    public function hydrated(): array
    {
        $rawItems = $this->rawItems();
        if ($rawItems === []) {
            return [];
        }

        $menu = $this->flattenMenu();
        $result = [];

        foreach ($rawItems as $item) {
            $product = $menu['products'][$item['productId']] ?? null;

            if ($product === null) {
                $result[] = [
                    'cartId' => $item['cartId'],
                    'name' => 'Produk tidak tersedia lagi',
                    'modifiers' => [],
                    'note' => $item['note'],
                    'qty' => $item['qty'],
                    'unitPrice' => 0,
                    'subtotal' => 0,
                    'available' => false,
                ];

                continue;
            }

            $modifiers = [];
            $modifiersTotal = 0;
            foreach ($item['modifierOptionIds'] as $optionId) {
                $option = $menu['options'][$optionId] ?? null;
                if ($option === null) {
                    continue;
                }
                $modifiers[] = [
                    'groupName' => $option['groupName'],
                    'optionName' => $option['name'],
                    'priceDelta' => $option['priceDelta'],
                ];
                $modifiersTotal += $option['priceDelta'];
            }

            $unitPrice = $product['basePrice'] + $modifiersTotal;

            $result[] = [
                'cartId' => $item['cartId'],
                'productId' => $product['id'],
                'name' => $product['name'],
                'imageUrl' => $product['imageUrl'],
                'modifiers' => $modifiers,
                'modifierOptionIds' => $item['modifierOptionIds'],
                'note' => $item['note'],
                'qty' => $item['qty'],
                'unitPrice' => $unitPrice,
                'subtotal' => $unitPrice * $item['qty'],
                'available' => true,
            ];
        }

        return $result;
    }

    public function subtotal(): int
    {
        return (int) array_sum(array_map(
            fn (array $i) => $i['available'] ? $i['subtotal'] : 0,
            $this->hydrated()
        ));
    }

    /** Payload siap kirim ke PosApi::createOrder() - hanya item yg masih available. */
    public function toOrderItems(): array
    {
        return array_values(array_map(
            fn (array $i) => [
                'productId' => $i['productId'],
                'qty' => $i['qty'],
                'note' => $i['note'],
                'modifierOptionIds' => $i['modifierOptionIds'],
            ],
            array_filter($this->hydrated(), fn (array $i) => $i['available'])
        ));
    }

    /** Ratakan menu nested (kategori->produk->modifierGroups->options) jadi lookup by id. */
    private function flattenMenu(): array
    {
        $products = [];
        $options = [];

        foreach ($this->api->menu()['categories'] ?? [] as $category) {
            foreach ($category['products'] ?? [] as $product) {
                $products[$product['id']] = $product;

                foreach ($product['modifierGroups'] ?? [] as $group) {
                    foreach ($group['options'] ?? [] as $option) {
                        $options[$option['id']] = [
                            'name' => $option['name'],
                            'priceDelta' => $option['priceDelta'],
                            'groupName' => $group['name'],
                        ];
                    }
                }
            }
        }

        return ['products' => $products, 'options' => $options];
    }

    private function rawItems(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function save(array $items): void
    {
        session([self::SESSION_KEY => $items]);
    }
}
