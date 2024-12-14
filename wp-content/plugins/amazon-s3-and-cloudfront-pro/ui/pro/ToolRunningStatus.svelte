<script>
	import {push} from "svelte-spa-router";
	import {urls} from "../js/stores";
	import {running, tools, toolsLocked} from "./stores";
	import {numToShortString, numToString} from "../js/numToString";
	import ProgressBar from "../components/ProgressBar.svelte";
	import ToolRunningButtons from "./ToolRunningButtons.svelte";

	/**
	 * Returns the currently running tool's details.
	 *
	 * @param {Object} tools
	 * @param {string} running
	 *
	 * @return {unknown}
	 */
	function runningTool( tools, running ) {
		return Object.values( tools ).find( ( tool ) => tool.id === running );
	}

	/**
	 * Get status description for tool.
	 *
	 * @param {Object} tool
	 * @param {boolean} isRunning
	 *
	 * @return {string}
	 */
	function toolStatus( tool, isRunning ) {
		if ( !isRunning ) {
			return "";
		}

		if ( tool.short_status_description ) {
			return tool.short_status_description;
		}

		return tool.busy_description;
	}

	$: isRunning = !!$running;
	$: tool = runningTool( $tools, $running );
	$: icon = tools.icon( tool, isRunning, true );

	// Buttons should be disabled if another tool is running, current tool is in process of pausing or cancelling, or all tools locked.
	$: disabled = isRunning && (($running && $running !== tool.id) || (tool.is_processing && tool.is_paused) || tool.is_cancelled || $toolsLocked);

	$: starting = !!(isRunning && tool.progress < 1 && !tool.is_paused);
	$: status = isRunning ? "(" + numToShortString( tool.queue.processed ) + "/" + numToShortString( tool.queue.total ) + ") " + toolStatus( tool, isRunning ) : "";
	$: title = isRunning ? tool.name + ": " + tool.progress + "% (" + numToString( tool.queue.processed ) + "/" + numToString( tool.queue.total ) + ")" : "";

	/**
	 * Returns the numeric percentage progress for the running job.
	 *
	 * @param {Object} tool
	 * @param {boolean} isRunning
	 *
	 * @return {number}
	 */
	function getPercentComplete( tool, isRunning ) {
		if ( isRunning ) {
			return tool.progress;
		}

		return 0;
	}

	$: percentComplete = getPercentComplete( tool, isRunning );
</script>

{#if tool}
	<div class="nav-status-wrapper tool-running">
		<div class="nav-status" {title} on:click={() => push("/tools")}>
			<p class="status-text" {title}>
				<strong>{tool.progress}%</strong>
				<span> {@html status}</span>
			</p>
			<ProgressBar
				{percentComplete}
				{starting}
				running={isRunning}
				paused={tool.is_paused}
				{title}
			/>
			<div class="animation-running" {title}>
				<img src="{$urls.assets + 'img/icon/' + icon}" alt="{tool.status_description}"/>
			</div>
		</div>
		<ToolRunningButtons {tool} {disabled} small/>
	</div>
{/if}
