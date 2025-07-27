<!DOCTYPE html>
<html>
<head>
    <title>Test Form</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <h1>Test Update PDF Form</h1>
    
    <form method="POST" action="{{ route('purchase-orders.update-pdf', 16) }}">
        @csrf
        @method('PUT')
        
        <div>
            <label>Provider Name:</label>
            <input type="text" name="provider_name" value="Test Provider" required>
        </div>
        
        <div>
            <label>Subtotal:</label>
            <input type="number" name="subtotal" value="1000000" required>
        </div>
        
        <div>
            <label>Total:</label>
            <input type="number" name="total" value="1190000" required>
        </div>
        
        <div>
            <label>Items:</label>
            <input type="text" name="items[0][description]" value="Test Item">
            <input type="number" name="items[0][quantity]" value="1">
            <input type="number" name="items[0][unit_price]" value="1000000">
            <input type="number" name="items[0][total]" value="1000000">
        </div>
        
        <button type="submit">Save</button>
    </form>
    
    @if(session('success'))
        <div style="color: green;">{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div style="color: red;">{{ session('error') }}</div>
    @endif
    
    @if($errors->any())
        <div style="color: red;">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>
</html>
