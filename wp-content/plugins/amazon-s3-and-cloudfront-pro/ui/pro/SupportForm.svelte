<script>
	import {diagnostics, notifications, strings} from "../js/stores";
	import {licence} from "./stores";
	import Button from "../components/Button.svelte";

	let email = "";
	let subject = "";
	let message = "";
	let includeDiagnostics = true;

	/**
	 * Potentially returns a reason that the Submit button is disabled.
	 *
	 * @param {string} email
	 * @param {string} subject
	 * @param {string} message
	 *
	 * @return {string}
	 */
	function getDisabledReason( email, subject, message ) {
		let reason = "";

		if ( !email || !subject || !message ) {
			reason = "Email, Subject and Message required.";
		}

		return reason;
	}

	$: disabledReason = getDisabledReason( email, subject, message );

	/**
	 * Handles a Submit button click.
	 *
	 * @param {Object} event
	 *
	 * @return {Promise<void>}
	 */
	async function submitSupportRequest( event ) {
		const formData = new FormData();

		formData.append( "email", email );
		formData.append( "subject", subject );
		formData.append( "message", message );

		if ( includeDiagnostics ) {
			formData.append( "local-diagnostic", "1" );
			formData.append( "local-diagnostic-content", $diagnostics );
		}

		let response;

		try {
			response = await fetch(
				$licence.support_url,
				{
					method: "POST",
					body: formData
				}
			);
		} catch ( error ) {
			const notice = $strings.send_email_post_error + error.message;

			notifications.add( {
				id: "support-send-email-response",
				type: "error",
				dismissible: true,
				only_show_on_tab: "support",
				message: notice
			} );

			return;
		}

		const json = await response.json();

		if ( json.hasOwnProperty( "errors" ) ) {
			for ( const [key, value] of Object.entries( json.errors ) ) {
				const notice = $strings.send_email_api_error + value;

				notifications.add( {
					id: "support-send-email-response",
					type: "error",
					dismissible: true,
					only_show_on_tab: "support",
					message: notice
				} );
			}

			return;
		}

		if ( json.hasOwnProperty( "success" ) && json.success === 1 ) {
			notifications.add( {
				id: "support-send-email-response",
				type: "success",
				dismissible: true,
				only_show_on_tab: "support",
				message: $strings.send_email_success
			} );

			email = "";
			subject = "";
			message = "";
			includeDiagnostics = true;

			return;
		}

		notifications.add( {
			id: "support-send-email-response",
			type: "error",
			dismissible: true,
			only_show_on_tab: "support",
			message: $strings.send_email_unexpected_error
		} );
	}
</script>

<label for="email" class="input-label">From</label>
<select name="email" id="email" bind:value={email}>
	{#each $licence.support_email_addresses as supportEmail}
		<option value={supportEmail}>{supportEmail}</option>
	{/each}
	<option value="">{$strings.select_email}</option>
</select>
<p class="note">{@html $strings.email_note}</p>
<input type="text" id="subject" name="subject" bind:value={subject} minlength="4" placeholder={$strings.email_subject_placeholder}>
<textarea id="message" name="message" bind:value={message} rows="8" placeholder={$strings.email_message_placeholder}></textarea>
<div class="actions">
	<div class="checkbox">
		<label for="include-diagnostics">
			<input type="checkbox" id="include-diagnostics" name="include-diagnostics" bind:checked={includeDiagnostics}>{$strings.attach_diagnostics}
		</label>
	</div>
	<Button primary on:click={submitSupportRequest} disabled={disabledReason} title={disabledReason}>{$strings.send_email}</Button>
</div>
<p class="note first">{$strings.having_trouble}</p>
<p class="note">{@html $strings.email_instead}</p>
