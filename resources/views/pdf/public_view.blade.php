<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->invoice_number }} - {{ $invoice->company->name }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Figtree', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-indigo-600 rounded-md flex items-center justify-center text-white font-bold">I</div>
                    <span class="font-bold text-gray-900 hidden sm:block">{{ config('app.name') }}</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500 hidden md:block">
                        {{ $invoice->invoice_number }} &middot; {{ number_format($invoice->total, 2) }} {{ $invoice->currency }}
                    </span>
                    <a 
                        href="{{ route('invoices.download', $invoice->id) }}" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-1 overflow-auto p-4 md:p-8 flex justify-center">
        <div class="bg-white shadow-2xl w-full max-w-[800px] min-h-[1131px] relative mb-12">
            <iframe 
                src="{{ route('invoices.preview_public', $invoice->share_token) }}" 
                class="w-full h-full absolute inset-0 border-0 pointer-events-none"
                style="min-height: 1131px;"
            ></iframe>
        </div>
    </main>

    <footer class="bg-white border-t py-6 text-center text-sm text-gray-500">
        <p>&copy; {{ now()->year }} {{ config('app.name') }}. All rights reserved.</p>
    </footer>
</body>
</html>
