import{a as r}from"./index.4776f7d5.js";import{d as i}from"./dannie-profile.e0152a9f.js";import{B as n}from"./Img.b806c655.js";import{k as o}from"./index.4a5acef5.js";import{S as l}from"./Book.7d439a03.js";import{n as c}from"./vueComponentNormalizer.58b0a173.js";var m=function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"aioseo-twitter-preview"},[e("div",{staticClass:"twitter-post"},[e("div",{staticClass:"twitter-header"},[e("div",{staticClass:"profile-photo"},[e("img",{attrs:{alt:"Dannie the Detective profile image",src:t.$getAssetUrl(t.dannieProfileImg)}})]),e("div",{staticClass:"poster"},[e("div",{staticClass:"poster-name"},[t._v(" "+t._s(t.appName)+" ")]),e("div",{staticClass:"poster-username"},[t._v(" @aioseopack ")])])]),e("div",{staticClass:"twitter-container",class:t.getCard==="summary_large_image"&&!t.image?"summary":t.getCard},[e("div",{staticClass:"twitter-content"},[e("div",{staticClass:"twitter-image-preview",style:{backgroundImage:t.getCard==="summary"&&t.canShowImage?`url('${t.image}')`:""}},[!t.loading&&(!t.image||!t.canShowImage)?e("svg-book"):t._e(),t.loading?e("core-loader"):t._e(),e("base-img",{directives:[{name:"show",rawName:"v-show",value:t.getCard==="summary_large_image"&&t.canShowImage,expression:"'summary_large_image' === getCard && canShowImage"}],attrs:{src:t.image,debounce:!1},on:{"can-show":t.maybeCanShow}})],1),e("div",{staticClass:"twitter-site-description"},[e("div",{staticClass:"site-title"},[t._t("site-title")],2),e("div",{staticClass:"site-description"},[t._t("site-description")],2),e("div",{staticClass:"site-domain"},[t._v(" "+t._s(t.$aioseo.urls.domain)+" ")])])])])])])},u=[];const p={components:{BaseImg:n,CoreLoader:o,SvgBook:l},props:{image:String,card:String,loading:{type:Boolean,default(){return!1}}},data(){return{dannieProfileImg:i,canShowImage:!1}},computed:{...r(["options"]),appName(){return"All in One SEO"},getCard(){return this.card==="default"?this.options.social.twitter.general.defaultCardType:this.card}},methods:{maybeCanShow(t){this.canShowImage=t}}},s={};var d=c(p,m,u,!1,_,null,null,null);function _(t){for(let a in s)this[a]=s[a]}const y=function(){return d.exports}();export{y as C};
