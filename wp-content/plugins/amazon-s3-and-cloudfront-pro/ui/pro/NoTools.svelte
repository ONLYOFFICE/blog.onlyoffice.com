<script>
	import {strings, urls} from "../js/stores";
	import Upsell from "../components/Upsell.svelte";

	let benefits = [
		{
			icon: $urls.assets + "img/icon/offload-remaining.svg",
			alt: "offload icon",
			text: $strings.tools_uppsell_benefits.offload,
		},
		{
			icon: $urls.assets + "img/icon/download.svg",
			alt: "download icon",
			text: $strings.tools_uppsell_benefits.download,
		},
		{
			icon: $urls.assets + "img/icon/remove-from-bucket.svg",
			alt: "remove from bucket icon",
			text: $strings.tools_uppsell_benefits.remove_bucket,
		},
		{
			icon: $urls.assets + "img/icon/remove-from-server.svg",
			alt: "remove from server icon",
			text: $strings.tools_uppsell_benefits.remove_server,
		},
	];
</script>

<Upsell {benefits}>
	<div slot="heading">{$strings.no_tools_header}</div>

	<div slot="description">{@html $strings.no_tools_description}</div>
</Upsell>
