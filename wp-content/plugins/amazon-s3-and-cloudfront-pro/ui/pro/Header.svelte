<script>
	import {push} from "svelte-spa-router";
	import {strings, urls} from "../js/stores";
	import {licence} from "./stores";
	import Header from "../components/Header.svelte";
	import Button from "../components/Button.svelte";
</script>

<Header>
	{#if $licence.is_set}
		{#if $licence.is_valid}
			<div class="licence-type">
				<img src={$urls.assets + "img/icon/licence-checked.svg"} alt={$strings.licence_checked}/>
				<a href={$urls.licenses} class="licence" target="_blank">{$licence.plan_plus_licence}</a>
			</div>
			<p>{@html $licence.customer}</p>
		{:else}
			<div class="licence-type">
				<img src={$urls.assets + "img/icon/error.svg"} alt={$strings.licence_error}/>
				<a href={$urls.licenses} class="licence" target="_blank">{$licence.status_description}</a>
			</div>
		{/if}
	{:else}
		<Button large primary on:click={() => push("/license")}>{$strings.activate_licence}</Button>
	{/if}
</Header>