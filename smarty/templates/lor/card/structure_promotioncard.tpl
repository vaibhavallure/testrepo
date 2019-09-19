<!-- Start Gmail Promo Tab annotations  -->
{literal}
<script type="application/ld+json">
[
    {
        "@context": "http://schema.org/",
        "@type": "Organization",
        "name": "MILLESIMA",
        "logo": "https://static.millesima.com/s3/contrib/common/promotion-card/logo.png"
     },
     {
        "@context": "http://schema.org/",
        "@type": "EmailMessage",
 {/literal}
        "subjectLine": "{$desc.titre}"
 {literal}
     },
     {
        "@context": "http://schema.org/",
        "@type": "DiscountOffer",
 {/literal}
        "description": "{$promotionCardDescription}",
        "discountCode": "{$promotionCardDiscountCode}",
        "availabilityStarts": "{$promotionCardDateStart}",
        "availabilityEnds": "{$promotionCardDateEnd}"
 {literal}
      },
      {
        "@context": "http://schema.org/",
        "@type": "PromotionCard",
   {/literal}
        "image": "{$promotionCardImageLink}"
      }
]
</script>
<!-- End Gmail Promo Tab annotations  -->