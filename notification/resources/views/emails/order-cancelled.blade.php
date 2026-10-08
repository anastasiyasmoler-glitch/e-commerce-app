<p>Your order@if(! empty($payload['order_id'])) {{ $payload['order_id'] }}@endif was cancelled.</p>
@if(($payload['reason'] ?? '') === 'out_of_stock')
    <p>The item is out of stock.</p>
@elseif(! empty($payload['reason']))
    <p>{{ $payload['reason'] }}</p>
@endif
