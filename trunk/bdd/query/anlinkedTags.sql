select  t.code
from flux_tag t
left join flux_tagtag ttsrc on ttsrc.tag_id_src = t.tag_id 
where ttsrc.tag_id_src is null
order by t.code