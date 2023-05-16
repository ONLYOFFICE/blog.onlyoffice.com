<script>
	import {strings} from "../js/stores";
	import {documentation} from "./stores";
</script>

{#if $documentation.length}
	<div class="documentation">
		<h3>{$strings.documentation_title}</h3>
		{#each $documentation as item}
			<a href={item.url} class="link" target="_blank">{item.title}</a>
		{/each}
	</div>
{/if}