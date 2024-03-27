(()=>{"use strict";const e=window.React,t=window.wc.wcBlocksRegistry,a=window.wc.wcSettings,n=window.wc.wcBlocksData,o=window.wp.data,s=window.wp.i18n,c=window.wp.htmlEntities,l=window.wp.element,i=(0,a.getSetting)("soleaspay_data",{}),r=(0,s.__)("Soleaspay","woo-gutenberg-products-block"),d=(0,s.__)("Pay with Soleaspay","woo-gutenberg-products-block"),m=(0,c.decodeEntities)((0,s.__)(i.title,"woo-gutenberg-products-block"))||r,p=(0,c.decodeEntities)((0,s.__)(i.description,"woo-gutenberg-products-block")||""),w=(0,c.decodeEntities)((0,s.__)(i.button_title,"woo-gutenberg-products-block"))||d,g=(0,c.decodeEntities)(i.icon||""),u=i.images,y=({imagesUrl:t,width:a=100,height:n=90})=>(0,e.createElement)("div",{className:"soleaspay-data-block-image-list"},t.map(((t,o)=>(0,e.createElement)("img",{className:"soleaspay-data-block-image-data",key:o,src:t,width:a,height:n,alt:`Image-${o+1}`,style:{margin:"2px"}})))),b=()=>(0,e.createElement)("img",{src:g,alt:"Icon-image"}),E=({checkoutStatus:t})=>{const{isComplete:a}=t,s=(0,o.useSelect)((e=>e(n.PAYMENT_STORE_KEY).getPaymentResult()),[]);return(0,l.useEffect)((()=>{if(a){const{result:e,soleaspay_response_data:t}=s.paymentDetails;"success"===e&&void 0!==t&&(document.querySelector("form").insertAdjacentHTML("beforebegin",t),document.getElementById("soleaspay_data_form").submit())}}),[a,s]),(0,e.createElement)("div",{className:"soleaspay-data-block"},(0,e.createElement)("div",{className:"soleaspay-data-block-description"},p),(0,l.useMemo)((()=>(0,e.createElement)(y,{imagesUrl:u})),[u]))},_={name:i.name,label:(0,e.createElement)((({components:t})=>{const{PaymentMethodIcons:a,PaymentMethodLabel:n}=t;return(0,e.createElement)("div",{style:{display:"flex",width:"100%",flexDirection:"row",alignContent:"space-between",justifyContent:"space-between",paddingRight:"60px"}},(0,e.createElement)(n,{icon:(0,e.createElement)(b,null),text:m}),(0,e.createElement)(a,{icons:(o=u,o.map(((e,t)=>({id:`soleaspay-icon-image-${t}`,alt:`Soleaspay Icon Image${t}`,src:e})))),align:"right"}));var o}),null),content:(0,e.createElement)(E,null),edit:(0,e.createElement)(E,null),canMakePayment:()=>!0,ariaLabel:m,placeOrderButtonLabel:w,supports:{features:i.supports}};(0,t.registerPaymentMethod)(_)})();