
{foreach from=$articles key=article item=value}

        <!-- spacer / c0a35171-4b7d-42f1-8693-85a2909c7835 -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;clear:both;width:640px" class="t_w100p m_w100p" role="presentation">
          <tr>
            <td height="20" style="height:20px;font-size:20px;line-height:20px" class="">
              &nbsp;
            </td>
          </tr>
        </table>
        <!-- EO - spacer - c0a35171-4b7d-42f1-8693-85a2909c7835 -->


  <!-- SECTION / Article supplementaire -->
  <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;background-color:#ffffff;width:640px" class="t_w100p m_w100p">
    <tr>
      <td style="">

        <!-- WRAPPER / aa6de0df-1e49-463c-be99-9d723ae5ec4f -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p">
          <tr>
            <td style="">

              <!-- COLUMN / 074a9ad3-496c-4cae-bdc3-303a0c1138e3 -->
              <table cellpadding="0" cellspacing="0" border="0" width="320" align="left" style="width:320px" class="t_w100p m_w100p" role="presentation">
                <tr>
                  <td>
                    <!-- image / e382072d-da63-4664-854e-310e7123911c -->
                    <table cellpadding="0" cellspacing="0" border="0" width="320" align="center" style="margin:auto;width:320px" class="t_w100p m_w100p" role="presentation">
                      <tr>
                        <td style="text-align:center">
                          <a href="{$articles.$article.url}" style="color:#000000;outline:none;border:none">
                            <img src="{if $articles.$article.artimgprim}http://cdn.millesima.com.s3.amazonaws.com/templates/articles/primeurs/article_{$articles.$article.imgnb}.jpg{else}http://cdn.millesima.com.s3.amazonaws.com/{$type_message}/{$codemessagegeneral}/article_{$articles.$article.imgnb}.jpg{/if}" alt="{$articles.$article.titre}" width="320" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:13px;display:block;border:none;margin-bottom:15px;" class="t_w100p m_w100p" />
                          </a>
                        </td>
                      </tr>
                    </table>
                    <!-- EO - image - e382072d-da63-4664-854e-310e7123911c -->

                  </td>
                </tr>
              </table>

              <!--[if gte mso 9]> </td><td style="vertical-align:top;border-collapse:collapse"><![endif]-->
              <!-- COLUMN / afcaf938-82f3-41ed-bd92-9731dc1de685 -->
              <table cellpadding="0" cellspacing="0" border="0" width="320" align="right" style="width:320px" class="t_w100p m_w100p" role="presentation">
                <tr>
                  <td>
                    <!-- WRAPPER / e06232e9-040b-431c-9d0a-42712af7c89b -->
                    <table cellpadding="0" cellspacing="0" border="0" width="260" align="center" style="margin:auto;width:260px" class="t_w100p m_w100p">
                      <tr>
                        <td style="">

                          <!-- text / 03247acb-6172-4b26-8236-da893b14aff8 -->
                          <table cellpadding="0" cellspacing="0" border="0" width="260" align="center" style="margin:auto;width:260px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                              <td style="font-family:Georgia,Times,Times New Roman,serif;font-weight:normal;color:#0a0a0a;text-align:center;font-size:25px;padding-top:15px;vertical-align:middle;line-height:30px" class=""t_pr8p t_pl8p m_pr8p m_pl8p crimsontext;{if $articles.$article.titreupper}text-transform:uppercase; {/if}"">
                              {$articles.$article.titre}
                              </td>
                            </tr>
                          </table>
                          <!-- EO - text - 03247acb-6172-4b26-8236-da893b14aff8 -->

                          <!-- text / bee3bb3f-8ceb-4578-aa44-f63b82dbe5f8 -->
                          <table cellpadding="0" cellspacing="0" border="0" width="260" align="center" style="margin:auto;width:260px" class="t_w100p m_w100p" role="presentation">
                            <tr>
                              <td style="font-family:Arial,Helvetica,sans-serif;color:#272727;text-align:justify;font-size:13px;padding-top:15px;padding-bottom:25px;vertical-align:middle;line-height:18px" class="t_fz15px t_pr8p t_pb30px t_pl8p t_lh21px m_fz15px m_pr8p m_pb30px m_pl8p m_lh21px">
                                {$articles.$article.text}
                              </td>
                            </tr>
                          </table>
                          <!-- EO - text - bee3bb3f-8ceb-4578-aa44-f63b82dbe5f8 -->
                          <!-- WRAPPER / c9e3365b-125b-407e-813c-41d182e4cd05 -->
                          <table cellpadding="0" cellspacing="0" border="0" width="200" align="left" style="width:200px" class="t_w100p m_w100p">
                            <tr>
                              <td style="">

                                <!-- cta / d2048ef5-4ce9-4971-9278-f275e895386e -->
                                <table cellpadding="0" cellspacing="0" border="0" width="200" align="center" style="margin:auto;width:200px" class="t_mauto t_w200px m_w200px" role="presentation">
                                  <tr>
                                    <td class="t_pb40px m_pb40px">
                                        {include file="$tpl/boutons/structure_btn.tpl" infos=$articles.$article}
                                    </td>
                                  </tr>
                                </table>
                                <!-- EO - cta - d2048ef5-4ce9-4971-9278-f275e895386e -->

                              </td>
                            </tr>
                          </table>
                          <!-- EO - WRAPPER / c9e3365b-125b-407e-813c-41d182e4cd05 -->
                        </td>
                      </tr>
                    </table>
                    <!-- EO - WRAPPER / e06232e9-040b-431c-9d0a-42712af7c89b -->

                  </td>
                </tr>
              </table>




                  </td>
          </tr>
        </table>



        <!-- Début astérisque -->
        <table cellpadding="0" cellspacing="0" border="0" width="640" align="center" style="margin:auto;width:640px" class="t_w100p m_w100p" role="presentation">
          {if $articles.$article.astart != ""}
            <tr>
              <td style="font-family:Arial,Helvetica,sans-serif;font-weight:normal;color:#000000;text-align:left;font-size:11px;padding-bottom:5px;padding-left:10px;vertical-align:middle;line-height:15px;margin-bottom:10px;" class=" sourcesanspro">
                {$articles.$article.astart}
              </td>
            </tr>
          {/if}
        </table>
        <!-- Fin astérisque -->

      </td>
    </tr>
  </table>
  <!-- EO - SECTION / Article supplementaire -->
{/foreach}