import{x as s,y as k,z as _}from"./app-core.62e9a672.js";import{h as c}from"./utils.f45e4b3e.js";import{A as d}from"./icon.18e8a700.js";import{_ as f}from"./vendor-other.3c19b488.js";import"./vendor-vue-ui.6921f61f.js";import"./vendor-lodash.88c383e9.js";const{addFilter:x}=window.wp.hooks,{BlockControls:p}=window.wp.blockEditor,{Button:l,ToolbarGroup:$,ToolbarButton:h}=window.wp.components,{Fragment:b,render:S,unmountComponentAtNode:w}=window.wp.element,{createHigherOrderComponent:A}=window.wp.compose,{select:u,useSelect:B}=window.wp.data,I="all-in-one-seo-pack",m={generateWithAI:f("Generate with AI",I),editWithAI:f("Edit with AI",I)};let y=!1;const g=(i,n={})=>{window.aioseoBus.$emit("do-post-settings-main-tab-change",{name:"aiContent"}),i.classList.add("is-busy"),i.disabled=!0;const e=_(),t=s();setTimeout(()=>{t.initiator=n?.initiator,(!t.initiator||!t.initiator.slug)&&t.resetInitiator(),e.isModalOpened="image-generator",i.classList.remove("is-busy"),i.disabled=!1},500)},z=()=>{s().extend.imageBlockToolbar&&(y||(x("editor.BlockEdit","aioseo/extend-image-block-toolbar",A(n=>e=>{const t=e.name==="core/image"&&e.attributes?.url,r=B(o=>!t||!e.attributes?.id?null:o("core").getEntityRecord("postType","attachment",e.attributes.id)||null,[`media-${e.attributes.id}`]);return t?c`
				<${b}>
					<${p}>
						<${$}>
							<${h}
								icon=${d}
								iconSize=${24}
								label=${m.editWithAI}
								onClick=${o=>{g(o.currentTarget,{initiator:{slug:"image-block-toolbar",wpMedia:r}})}}
								style=${{maxHeight:"90%",alignSelf:"center",padding:"0"}}
							/>
						</${$}>
					</${p}>

					<${n} ...${e} />
				</${b}>`:c`<${n} ...${e} />`},"extendImageBlockToolbar")),y=!0))},L=()=>{if(!s().extend.imageBlockPlaceholder)return;const n=u("core/block-editor").getSelectedBlock();if(!n||n.name!=="core/image"||n.attributes?.url)return;const t=k().getElementById(`block-${n.clientId}`),r=t?.querySelector(".components-form-file-upload");if(!r||t?.querySelector(".aioseo-ai-image-generator-btn"))return;const o=document.createElement("div");S(c`
			<${l}
				className=${"aioseo-ai-image-generator-btn"}
				variant=${"secondary"}
				icon=${d}
				iconSize=${"20"}
				__next40pxDefaultSize=${!0}
			>
				${m.generateWithAI}
			</${l}>`,o);const a=o.firstChild?.cloneNode(!0);a&&(r.after(a),a.addEventListener("click",()=>{g(a,{initiator:{slug:"image-block-placeholder"}})})),w(o),o.remove()},N=()=>{if(!s().extend.featuredImageButton||u("core/edit-post").getActiveGeneralSidebarName()!=="edit-post/document")return;if(u("core/editor").getEditedPostAttribute("featured_media")){document.querySelector(".aioseo-ai-image-generator-btn-featured-image")?.remove();return}setTimeout(()=>{const e=document.querySelector(".editor-post-featured-image__container"),t=e?.querySelector("button");if(!t||e?.querySelector(".aioseo-ai-image-generator-btn-featured-image"))return;e.style.display="flex",e.style.gap="8px";const r=document.createElement("div");S(c`
				<${l}
					className=${"aioseo-ai-image-generator-btn-featured-image"}
					variant=${"secondary"}
					icon=${d}
					iconSize=${"20"}
					__next40pxDefaultSize=${!0}
					title=${m.generateWithAI}
				/>`,r);const o=r.firstChild?.cloneNode(!0);o&&(t.after(o),o.addEventListener("click",()=>{g(o,{initiator:{slug:"featured-image-btn"}})})),w(r),r.remove()})};export{N as extendFeaturedImageButton,L as extendImageBlockPlaceholder,z as extendImageBlockToolbar};
