import{s as c,t as w}from"./app-core.82d0a9b8.js";import{h as i}from"./utils.825d3528.js";import{a as u}from"./icon.6c0bd3d8.js";import{_ as g}from"./vendor-other.2cdd5822.js";import"./vendor-vue-ui.02763c14.js";import"./vendor-datetime.430013a3.js";import"./vendor-lodash.f9514987.js";const{addFilter:k}=window.wp.hooks,{BlockControls:f}=window.wp.blockEditor,{Button:s,ToolbarGroup:p,ToolbarButton:_}=window.wp.components,{Fragment:$,render:S,unmountComponentAtNode:y}=window.wp.element,{createHigherOrderComponent:h}=window.wp.compose,{select:l,useSelect:x}=window.wp.data,b="all-in-one-seo-pack",d={generateWithAI:g("Generate with AI",b),editWithAI:g("Edit with AI",b)};let I=!1;const m=(a,r={})=>{window.aioseoBus.$emit("do-post-settings-main-tab-change",{name:"aiContent"}),a.classList.add("is-busy"),a.disabled=!0;const e=w(),t=c();setTimeout(()=>{t.initiator=r?.initiator,(!t.initiator||!t.initiator.slug)&&t.resetInitiator(),e.isModalOpened="image-generator",a.classList.remove("is-busy"),a.disabled=!1},500)},q=()=>{c().extend.imageBlockToolbar&&(I||(k("editor.BlockEdit","aioseo/extend-image-block-toolbar",h(r=>e=>{const t=e.name==="core/image"&&e.attributes?.url,n=x(o=>!t||!e.attributes?.id?null:o("core").getEntityRecord("postType","attachment",e.attributes.id)||null,[`media-${e.attributes.id}`]);return t?i`
				<${$}>
					<${f}>
						<${p}>
							<${_}
								icon=${u}
								iconSize=${24}
								label=${d.editWithAI}
								onClick=${o=>{m(o.currentTarget,{initiator:{slug:"image-block-toolbar",wpMedia:n}})}}
								style=${{maxHeight:"90%",alignSelf:"center",padding:"0"}}
							/>
						</${p}>
					</${f}>

					<${r} ...${e} />
				</${$}>`:i`<${r} ...${e} />`},"extendImageBlockToolbar")),I=!0))},L=()=>{if(!c().extend.imageBlockPlaceholder)return;const r=l("core/block-editor").getSelectedBlock();if(!r||r.name!=="core/image"||r.attributes?.url)return;const e=document.getElementById(`block-${r.clientId}`),t=e?.querySelector(".components-form-file-upload");if(!t||e?.querySelector(".aioseo-ai-image-generator-btn"))return;const n=document.createElement("div");S(i`
			<${s}
				className=${"aioseo-ai-image-generator-btn"}
				variant=${"secondary"}
				icon=${u}
				iconSize=${"20"}
				__next40pxDefaultSize=${!0}
			>
				${d.generateWithAI}
			</${s}>`,n);const o=n.firstChild?.cloneNode(!0);o&&(t.after(o),o.addEventListener("click",()=>{m(o,{initiator:{slug:"image-block-placeholder"}})})),y(n),n.remove()},N=()=>{if(!c().extend.featuredImageButton||l("core/edit-post").getActiveGeneralSidebarName()!=="edit-post/document")return;if(l("core/editor").getEditedPostAttribute("featured_media")){document.querySelector(".aioseo-ai-image-generator-btn-featured-image")?.remove();return}setTimeout(()=>{const e=document.querySelector(".editor-post-featured-image__container"),t=e?.querySelector("button");if(!t||e?.querySelector(".aioseo-ai-image-generator-btn-featured-image"))return;e.style.display="flex",e.style.gap="8px";const n=document.createElement("div");S(i`
				<${s}
					className=${"aioseo-ai-image-generator-btn-featured-image"}
					variant=${"secondary"}
					icon=${u}
					iconSize=${"20"}
					__next40pxDefaultSize=${!0}
					title=${d.generateWithAI}
				/>`,n);const o=n.firstChild?.cloneNode(!0);o&&(t.after(o),o.addEventListener("click",()=>{m(o,{initiator:{slug:"featured-image-btn"}})})),y(n),n.remove()})};export{N as extendFeaturedImageButton,L as extendImageBlockPlaceholder,q as extendImageBlockToolbar};
