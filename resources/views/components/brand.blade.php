@props(['href' => route('home')])

<a href="{{ $href }}" {{ $attributes->class(['brand']) }}>
    <img class="brand-mark" src="{{ asset('images/resolveiq-mark.svg') }}" width="38" height="38" alt="">
    <span class="brand-text" dir="ltr">Resolve<span>IQ</span></span>
</a>
