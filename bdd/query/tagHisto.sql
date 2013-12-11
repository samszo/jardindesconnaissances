select t.code, t.desc, d.maj, count(*) nb, count(distinct utd.uti_id) nbUti, count(distinct utd.doc_id) nbDoc
from flux_tag t
inner join flux_utitagdoc utd on utd.tag_id = t.tag_id
inner join flux_doc d on utd.doc_id = d.doc_id
where t.code like "%information%" AND t.code like "%communication%"
group by d.maj
order by nb desc