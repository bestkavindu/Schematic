<div class="form-card @if($sent) sent @endif" id="formCard">
  <div class="form-body">
    <h2>Send us a message</h2>
    <p class="lead-p">Fill out the form and we'll route it to the right person.</p>

    <form wire:submit="send" novalidate>
      <div class="fld">
        <div class="lbl">What's this about?</div>
        <div class="topics" id="topics">
          @php
            $topicLabels = ['Sales' => 'Sales & pricing', 'Support' => 'Technical support', 'Demo' => 'Book a demo', 'Partnership' => 'Partnership', 'Other' => 'Other'];
          @endphp
          @foreach ($topics as $t)
            <button type="button" wire:click="selectTopic('{{ $t }}')" @class(['topic', 'on' => $topic === $t])>{{ $topicLabels[$t] ?? $t }}</button>
          @endforeach
        </div>
      </div>

      <div class="fld fld-2">
        <div @class(['fld', 'err' => $errors->has('name')]) id="fName" style="margin-bottom:0;">
          <label class="lbl" for="name">Full name</label>
          <input @class(['in', 'invalid' => $errors->has('name')]) id="name" type="text" wire:model.blur="name" placeholder="Amir Karimi" autocomplete="name" />
          <div class="fld-err"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('name'){{ $message }}@else Please enter your name @enderror</span></div>
        </div>
        <div @class(['fld', 'err' => $errors->has('email')]) id="fEmail" style="margin-bottom:0;">
          <label class="lbl" for="email">Work email</label>
          <input @class(['in', 'invalid' => $errors->has('email')]) id="email" type="email" wire:model.blur="email" placeholder="you@company.com" autocomplete="email" />
          <div class="fld-err"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('email'){{ $message }}@else Enter a valid email @enderror</span></div>
        </div>
      </div>

      <div class="fld fld-2">
        <div class="fld" style="margin-bottom:0;">
          <label class="lbl" for="company">Company <span class="opt">optional</span></label>
          <input class="in" id="company" type="text" wire:model="company" placeholder="Acme Inc." autocomplete="organization" />
        </div>
        <div class="fld" style="margin-bottom:0;">
          <label class="lbl" for="size">Team size <span class="opt">optional</span></label>
          <select class="in" id="size" wire:model="teamSize">
            <option value="">Select…</option>
            <option>Just me</option>
            <option>2–10</option>
            <option>11–50</option>
            <option>51–200</option>
            <option>200+</option>
          </select>
        </div>
      </div>

      <div @class(['fld', 'err' => $errors->has('message')]) id="fMsg">
        <label class="lbl" for="message">Message</label>
        <textarea @class(['in', 'invalid' => $errors->has('message')]) id="message" wire:model.blur="message" placeholder="Tell us a little about what you're building…"></textarea>
        <div class="fld-err"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg><span>@error('message'){{ $message }}@else Please add a short message @enderror</span></div>
      </div>

      {{-- Honeypot: hidden from humans, attractive to bots. --}}
      <div aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">
        <label>Website<input type="text" tabindex="-1" autocomplete="off" wire:model="website" /></label>
      </div>

      <label @class(['consent', 'err' => $errors->has('consent')]) id="consentRow">
        <input type="checkbox" id="consent" wire:model="consent" />
        <span class="box"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
        <span>I agree to Schematic's <a href="{{ route('legal.privacy') }}">Privacy Policy</a> and consent to being contacted about my request.</span>
      </label>

      <button class="btn-send" type="submit" id="sendBtn" wire:loading.class="loading" wire:loading.attr="disabled" wire:target="send">
        <span class="lbltxt">Send message</span>
        <span class="spin"></span>
      </button>
    </form>
  </div>

  <!-- success -->
  <div class="form-success">
    <div class="success-ic"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
    <h2>Message sent — thank you!</h2>
    <p>We've received your note and will reply to <b>{{ $sentEmail ?: 'your inbox' }}</b> shortly.</p>
    <button class="btn btn-ghost" type="button" wire:click="sendAnother">Send another message</button>
  </div>
</div>
