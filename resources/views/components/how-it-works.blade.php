<section id="how-it-works" class="section"><x-section-heading eyebrow="FROM TO-DO TO TA-DA" title="A simpler way to care for your home" description="Our planned booking experience, in four straightforward steps." /><div class="steps">
@foreach (['Choose a Service' => 'Start with what your home needs.', 'Find a Professional' => 'Explore the right skills for the job.', 'Book a Convenient Time' => 'Choose a time that fits your day.', 'Get the Problem Solved' => 'Let skilled hands take care of the rest.'] as $title => $text)
<article><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $title }}</h3><p>{{ $text }}</p></article>
@endforeach
</div></section>
