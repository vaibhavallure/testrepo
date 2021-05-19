<!-- Start Gmail Promo Tab annotations code -->
{literal}
<script type="application/ld+json">
    [{
        "@context": "http://schema.org/",
        "@type": "Organization",
        "name": "MILLESIMA",
        // If showing a logo, we recommend using an https URL.
        // It's not a requirement today, but may be in the future.
        "logo": "https://static.millesima.com/s3/contrib/common/promotion-card/logo.png"
     },{
        "@context": "http://schema.org/",
        "@type": "EmailMessage",
 {/literal}
        // Alternative subject line, coming soon.
        "subjectLine": "{$desc.titre}"
 {literal}
     },{
        "@context": "http://schema.org/",
        "@type": "DiscountOffer",
 {/literal}
     // Describe your discount, this will be shown as a badge (eg "25% off" or "free shipping")
        "description": "{$promotionCardDescription}",
        "discountCode": "{$promotionCardDiscountCode}",
        "availabilityStarts": "{$promotionCardDateStart}",
        "availabilityEnds": "{$promotionCardDateEnd}"
 {literal}
      },{
        // Promotion card with single image.
        // We recommend using an https URL.  It's not a requirement today, but may be in the future.
        // Any image size will work and will just be cropped automatically.
        // GIF & WEBP images are not supported and will be filtered out.
        // Sample image is 538x138, 3.9 aspect ratio
        "@context": "http://schema.org/",
        "@type": "PromotionCard",
   {/literal}
        "image": "{$promotionCardImageLink}"
      }]
</script>
<!-- End Gmail Promo Tab annotations code -->