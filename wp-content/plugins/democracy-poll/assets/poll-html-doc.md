# Front-end poll HTML structure

This document describes the complete HTML structure that Democracy Poll may render on the front end. It is intended primarily as a reference for CSS development.

The main source of the markup is `classes/Poll_Renderer.php`. Some elements are dynamically added by JavaScript from `assets/js/`.

Notation:

- `[...]` — an optional may exist may not - depends on related condition.
- `{{...}}` — integrated part that described in separate sections.

## Complete structure of a single poll

```html
<div class="democracy" id="democracy-{POLL_ID}">
	<strong class="dem-poll-title">Poll question</strong>

	<div class="dem-screen vote|voted">
		{{Voting screen | Results screen}} (see below)
	</div>

	<div class="dem-poll-note">
		<p>...</p>
		[<p>...</p>]
	</div>

	[<a class="dem-edit-link" href="..." title="Edit poll"><svg>...</svg></a>]

	<div class="dem-loader"><div><svg>...</svg></div></div>
</div><!--democracy-->

(Page-cache markup. JS copies the selected cache screen into `.dem-screen`.
Styles should target the `.dem-screen` rather than relying only on `.dem-screen-cache`.)
[ 
	<div class="dem-cache-screens" style="display:none;">
		<div class="dem-screen-cache voted">...</div>
		<div class="dem-screen-cache vote">...</div>
	</div>
]
```

## Voting screen

```html
<div class="dem-screen vote">
	<div class="dem-vote-wrap" [data-is_auto_vote="1"]>
		<ul class="dem-vote">
			<li class="dem-answer-item [dem-disabled]" data-aid="{ANSWER_ID}">
				<label class="dem__radio_label | dem__checkbox_label">
					<input class="dem__radio | dem__checkbox"
				       type="radio | checkbox"
				       value="{ANSWER_ID}"
				       [checked] [disabled]
					>
					<span class="dem__spot"></span>
					Answer text
				</label>
			</li>

			<li class="dem-add-answer [dem-disabled]">
				<a class="dem-link dem-add-answer-link" href="#">Add your answer</a>

				[<span class="dem-add-answer-close">×</span>]
				[<input type="text" class="dem-add-answer-txt" value="" [disabled]>]
			</li>

			[<span class="dem__collapser" class-state="collapsed | expanded"><span class="arr"></span></span>]
		</ul>

		<div class="dem-bottom dem-vote-bottom">
			[
				<div class="dem-vote-button" [style="display:none"]>
					<input class="dem-button {btn_class}" type="button" value="Vote">
				</div>
				|
				<div class="dem-voted-button">
					<input class="dem-button {btn_class}" type="button" value="Already voted..." disabled>
				</div>
				|
				<span class="dem-revote-button-wrap" [style="display:none"]>
		            <input class="dem-button dem-revote-button dem-revote-link {btn_class}" type="button" value="Revote">
		        </span>
				|
				<div class="dem-notice-inline">Only registered users can vote...</div>
			]

			[<a href="#" class="dem-link dem-results-link">Results</a>]
		</div>

	</div>

	[<div class="dem-loader" style="display:table;"><div><svg>...</svg></div></div>]
</div>
```


## Results screen

```html
<div class="dem-screen voted">
	<ul class="dem-answers" data-voted_txt="This is your vote.">
		<li class="[dem-winner] [dem-voted-this] [dem-novoted]" title="...">
			<div class="dem-label">
				Answer text

				[<sup class="dem-star" title="The answer was added by a visitor">*</sup>]

				<span class="dem-label-percent-txt">
					50%, 10 <span class="votxt">votes</span>
				</span>
			</div>

			<div class="dem-graph">
				<div class="dem-fill" data-width="50% | 1px" | style="width:50% | 1px"></div>

				<div class="dem-votes-txt">
					<span class="dem-votes-txt-votes">
						10 <span class="votxt">votes</span>
					</span>

					[<span class="dem-votes-txt-percent">50%</span>]
				</div>

				<div class="dem-percent-txt">10 votes - 50% of all votes</div>
			</div>
		</li>

		[<span class="dem__collapser" class-state="collapsed | expanded"><span class="arr"></span></span>]
	</ul>

	<div class="dem-bottom dem-results-bottom">
		<div class="dem-poll-info">
			<div class="dem-total-votes">Total Votes: 10</div>

			[<div class="dem-users-voted">Voters: 8</div>]

			<div class="dem-date" title="Begin">
				<span class="dem-begin-date">...</span>
				[ - <span class="dem-end-date" title="End">...</span>]
			</div>

			[<div class="dem-added-by-user"><span class="dem-star">*</span> - added by visitor</div>]

			[<div>Voting is closed</div>]

			[<a class="dem-archive-link dem-link" href="...">Polls Archive</a>]
		</div>

		[
			<button type="button" class="dem-button dem-vote-link {custom class}">Vote</button>
			|
			<span class="dem-revote-button-wrap">
				<input class="dem-button dem-revote-button dem-revote-link {custom class}" type="button" value="Revote">
			</span>
			|
			<div class="dem-notice-inline">Only registered users can vote...</div>
		]
	</div><!--/dem-bottom-->

	[<div class="dem-loader" style="display:table;">...</div>]
</div>
```


## Notices

The page contains one reusable notice template outside the poll blocks:

```html
<template class="dem_notice_template_js">
	<div class="dem-notice dem_notice_js">
		<button type="button" class="dem-notice-close dem_notice_close_js" aria-label="Close">&times;</button>
		<div class="dem-notice-message dem_notice_message_js"></div>
	</div>
</template>
```

JavaScript stores the current notice status and HTML in per-poll state, clones this template, and prepends the notice to `.democracy_js`.



## Poll archive

Default archive wrapper:

```html
<div class="dem-archives">
	<div class="dem-elem-wrap">
		<div class="democracy">...</div>

		<div class="dem-moreinfo">
			<b>From posts:</b>
			<ul>
				<li><a href="...">...</a></li>
				[<li><a href="...">...</a></li>]
			</ul>
		</div>
	</div>

	[<div class="dem-elem-wrap">...</div>]
</div>

[<div class="dem-paging">...</div>]
```
