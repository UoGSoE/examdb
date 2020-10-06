@if ($course->isFullyApproved())
<span class="tag is-success has-text-weight-bold">Fully Approved</span>
@elseif ($course->isFullyApprovedByModerator())
<span class="tag is-info has-text-weight-bold">Moderator Approved</span>
@elseif ($course->isApprovedByModerator('main'))
<span class="tag is-link has-text-weight-bold">Moderator Approved Main</span>
@elseif ($course->isApprovedByModerator('resit'))
<span class="tag is-link has-text-weight-bold">Moderator Approved Resit</span>
@endif
@if ($course->isntFullyApproved())
    @if ($course->isApprovedByExternal('main'))
        <span class="tag is-success has-text-weight-bold">External Approved Main</span>
    @endif
    @if ($course->isApprovedByExternal('resit'))
        <span class="tag is-success has-text-weight-bold">External Approved Resit</span>
    @endif
@endif
