select count(d.doc_id) nbDoc, sum(length(d.data)) nbOct
 , DATE_FORMAT(d.maj, "%Y-%m-%d %H:%i:%s") temps
from flux_doc d
group by temps
order by nbDoc DESC