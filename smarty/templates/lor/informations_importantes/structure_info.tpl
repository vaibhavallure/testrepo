<div id="MASECTION" macontenteditable="true" maconstraint="MASTER.TYPE_CONTACT <> 2" maparameter="" matype="" mahidediv="TRUE">
<!-- Début Espace -->
<table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
  <tr>
    <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
      &nbsp;
    </td>
  </tr>
</table>
<!-- Fin Espace -->

  <!--Début bandeau -->
  <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#090909;width:640px" class="t_w100p m_w100p">

    <tr>
      <td style="">

        <!-- Titre -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
          {if $bdheader.title != ""}
          <tr>
            <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:normal;color:#ffffff;text-align:center;font-size:25px;padding-top:25px;padding-bottom:10px;vertical-align:middle;line-height:27px" class="m_fz18px m_lh20px crimsontext">
              {$bdheader.title}
            </td>
          </tr>
          {/if}
        </table>
        <!-- Fin Titre -->

        <!-- Code promo -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
          {if $bdheader.detail != "" }
          <tr>
            <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#9c9487;text-align:center;text-transform:uppercase;font-size:13px;padding-bottom:15px;vertical-align:middle;letter-spacing:3px;line-height:15px" class=" sourcesanspro">
              {$bdheader.detail}
            </td>
          </tr>
          {/if}
        </table>
        <!-- Fin code promo -->

        <!-- Astérisque -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
          {if $bdheader.asterisque != "" }
          <tr>
            <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#ffffff;text-align:left;font-size:11px;padding-bottom:5px;padding-left:10px;vertical-align:middle;line-height:15px" class=" sourcesanspro">
              {$bdheader.asterisque}
            </td>
          </tr>
          {/if}
        </table>
        <!-- Fin astérique -->

      </td>
    </tr>
  </table>
  <!-- Fin bandeau-->
</div>
