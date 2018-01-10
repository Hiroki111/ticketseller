<h1>{{ $concert->title }}</h1>
<h2>{{ $concert->subtitle }}</h2>
<p>{{ $concert->date->format('F j, Y') }}</p>
<p>Doors at {{ $concert->date->formatted_date }}</p>
<p>{{ number_format($concert->ticket_price / 100, 2) }}</p>
<p>{{ $concert->venue }}</p>
<p>{{ $concert->address }}</p>
<p>{{ $concert->suburb }}</p>
<p>{{ $concert->state }}</p>
<p>{{ $concert->zip }}</p>
<p>{{ $concert->additional_information }}</p>
