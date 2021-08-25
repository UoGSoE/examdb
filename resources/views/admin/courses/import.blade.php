@extends('layouts.app')

@section('content')

<h3 class="title is-3">Import Course Information</h3>

<h4 class="title is-4">Format:</h4>
<pre>
    Course Code | Course Name | Discipline | Semester | Setters GUIDs | Setters Names               | Moderators GUIDs | Moderators Names             | Externals Emails  | Externals Names | Examined?
    ENG1234     | Lasers      | Elec       | 1        | abc1x,trs80y  | Anne Smith, Debbie McTavish | bob1q,lol9s      | Bob Syeruncle, Lola McGovern | jenny@example.com | Jenny Smith     | Y
</pre>

<hr>

<form action="{{ route('course.import.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="field">
        <div class="file">
            <label class="file-label">
              <input class="file-input" type="file" name="sheet">
              <span class="file-cta">
                <span class="file-icon">
                  <i class="fas fa-upload"></i>
                </span>
                <span class="file-label">
                  Choose a file…
                </span>
              </span>
            </label>
          </div>
    </div>
    <hr>
    <div class="field">
        <div class="control">
            <button class="button">Upload</button>
        </div>
    </div>
</form>
@endsection
