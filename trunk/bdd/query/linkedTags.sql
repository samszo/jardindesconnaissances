select  tsrc.code, count(tdst.tag_id) nb
from flux_tagtag tt
inner join flux_tag tsrc on tsrc.tag_id = tt.tag_id_src
inner join flux_tag tdst on tdst.tag_id = tt.tag_id_dst
group by tsrc.code
order by nb desc