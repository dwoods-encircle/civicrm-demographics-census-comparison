<div class="crm-block crm-content-block">
  <div class="crm-submit-buttons">
    <a class="button" href="{$settingsUrl}">{ts}Configure{/ts}</a>
  </div>
  <h2>{ts}Membership Demographics vs Census{/ts}</h2>
  <p>
    <strong>{ts}Dataset{/ts}:</strong> {$dataset}
    &nbsp;|&nbsp;
    <strong>{ts}Geography{/ts}:</strong> {$geoCode}
    &nbsp;|&nbsp;
    <strong>{ts}Total Members Counted{/ts}:</strong> {$totalMembers}
  </p>

  <h3>{ts}Gender{/ts}</h3>
  <table class="report-layout display">
    <thead>
      <tr>
        <th>{ts}Category{/ts}</th>
        <th style="text-align:right">{ts}% Membership{/ts}</th>
        <th style="text-align:right">{ts}% Area{/ts}</th>
        <th style="text-align:right">{ts}Index{/ts}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$genderRows item=row}
        <tr>
          <td>{$row.label|escape}</td>
          <td style="text-align:right">{math equation="x" x=$row.member_pct format="%.2f"}%</td>
          <td style="text-align:right">{math equation="x" x=$row.area_pct format="%.2f"}%</td>
          <td style="text-align:right">
            {if $row.index < 100}
              <span style="color:#b30000; font-weight:bold;">{$row.index}</span>
            {elseif $row.index > 100}
              <span style="color:#006600; font-weight:bold;">{$row.index}</span>
            {else}
              {$row.index}
            {/if}
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>

  <h3 style="margin-top:2em">{ts}Ethnicity{/ts}</h3>
  <table class="report-layout display">
    <thead>
      <tr>
        <th>{ts}Category{/ts}</th>
        <th style="text-align:right">{ts}% Membership{/ts}</th>
        <th style="text-align:right">{ts}% Area{/ts}</th>
        <th style="text-align:right">{ts}Index{/ts}</th>
      </tr>
    </thead>
    <tbody>
      {foreach from=$ethRows item=row}
        <tr>
          <td>{$row.label|escape}</td>
          <td style="text-align:right">{math equation="x" x=$row.member_pct format="%.2f"}%</td>
          <td style="text-align:right">{math equation="x" x=$row.area_pct format="%.2f"}%</td>
          <td style="text-align:right">
            {if $row.index < 100}
              <span style="color:#b30000; font-weight:bold;">{$row.index}</span>
            {elseif $row.index > 100}
              <span style="color:#006600; font-weight:bold;">{$row.index}</span>
            {else}
              {$row.index}
            {/if}
          </td>
        </tr>
      {/foreach}
    </tbody>
  </table>
</div>
