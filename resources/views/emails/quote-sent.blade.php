<p>مرحباً {{ $quote->client?->name }},</p>
<p>تم إرسال عرض السعر رقم <strong>{{ $quote->quote_number }}</strong> بقيمة <strong>{{ number_format($quote->total, 2) }}</strong>.</p>
<p>يمكنك مراجعة العرض من الرابط العام التالي:</p>
<p><a href="{{ route('quotes.public', $quote->public_token) }}">{{ route('quotes.public', $quote->public_token) }}</a></p>
<p>كما أرفقنا نسخة PDF للرجوع إليها.</p>
