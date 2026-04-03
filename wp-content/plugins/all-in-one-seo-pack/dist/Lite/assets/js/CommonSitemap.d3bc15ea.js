const u=(r=1,e=5e4)=>({validateLinksPerIndex:n=>{if(n===""||n===null||n===void 0)return r;const t=parseInt(n,10);return isNaN(t)||t<r?r:t>e?e:t}});export{u};
