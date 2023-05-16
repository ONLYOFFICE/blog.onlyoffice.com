<script>
	import {link} from "svelte-spa-router";
	import {counts, strings, urls} from "../js/stores";
	import {licence, offloadRemainingWithCount, running, tools} from "./stores";
	import Nav from "../components/Nav.svelte";
	import OffloadStatus from "../components/OffloadStatus.svelte";
	import ToolRunningStatus from "./ToolRunningStatus.svelte";
	import OffloadStatusFlyout from "../components/OffloadStatusFlyout.svelte";
	import PanelRow from "../components/PanelRow.svelte";
	import Button from "../components/Button.svelte";

	let flyoutButton;
	let expanded = false;
	let hasFocus = false;

	/**
	 * Get a message describing why the offload remaining button is disabled, if it is.
	 *
	 * @param {Object} licence
	 * @param {Object} counts
	 *
	 * @return {string}
	 */
	function getOffloadRemainingDisabledMessage( licence, counts ) {
		if ( !licence.is_set ) {
			return $strings.no_licence;
		}

		if ( counts.total < 1 ) {
			return $strings.no_media;
		}

		if ( counts.not_offloaded < 1 ) {
			return $strings.all_media_offloaded;
		}

		if (
			licence.limit_info.counts_toward_limit &&
			licence.limit_info.total > 0 &&
			licence.limit_info.limit > 0 &&
			licence.limit_info.total >= licence.limit_info.limit
		) {
			if ( licence.limit_info.total > licence.limit_info.limit ) {
				return $strings.licence_limit_exceeded;
			}

			return $strings.licence_limit_reached;
		}

		return "";
	}

	$: offloadRemainingDisabledMessage = getOffloadRemainingDisabledMessage( $licence, $counts );

	/**
	 * Close the flyout panel and kick off the offloader.
	 *
	 * The panel is closed so that it does not pop back open without focus on completion.
	 */
	function startOffload() {
		expanded = false;
		tools.start( $tools.uploader );
	}
</script>

<Nav>
	{#if !!$running}
		<ToolRunningStatus/>
	{:else}
		<OffloadStatus bind:flyoutButton bind:expanded bind:hasFocus>
			<svelte:fragment slot="flyout">
				<OffloadStatusFlyout bind:expanded bind:hasFocus bind:buttonRef={flyoutButton}>
					<svelte:fragment slot="footer">
						<PanelRow footer class="offload-remaining">
							<Button
								primary
								disabled={offloadRemainingDisabledMessage}
								title={offloadRemainingDisabledMessage}
								on:click={startOffload}
							>
								{$offloadRemainingWithCount}
							</Button>
						</PanelRow>

						<PanelRow footer class="licence">
							<div class="details">
								<p class="title">{$strings.plan_usage_title}</p>
								<p>{$licence.plan_usage}</p>
							</div>
							{#if !$licence.is_set}
								<a href="/license" use:link>
									{$strings.activate_licence}
								</a>
							{:else if $licence.limit_info.limit !== 0}
								<a href={$urls.licenses} target="_blank" class="upgrade">
									{$strings.upgrade_plan_cta}
								</a>
							{/if}
						</PanelRow>
					</svelte:fragment>
				</OffloadStatusFlyout>
			</svelte:fragment>
		</OffloadStatus>
	{/if}
</Nav>
