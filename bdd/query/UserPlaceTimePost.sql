SELECT *, DATEDIFF(myDate, firstDate) TimePlace, DATEDIFF(lastDate, firstDate) TimeSpace
FROM
(
SELECT ud.doc_id, COUNT(DISTINCT ud.uti_id) nbUti, MAX(ud.maj) lastDate, MIN(ud.maj) firstDate, udMe.maj myDate
FROM flux_utidoc ud
INNER JOIN flux_utidoc udMe on udMe.doc_id = ud.doc_id and udMe.uti_id = 1
INNER JOIN flux_utitagdoc utd on utd.doc_id = ud.doc_id and utd.uti_id = ud.uti_id 
INNER JOIN    
GROUP BY ud.doc_id
) ptp
order by nbUti desc
