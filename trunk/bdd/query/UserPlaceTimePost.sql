SELECT *, DATEDIFF(myDate, firstDate) TimePlace, DATEDIFF(lastDate, firstDate) TimeSpace from
(
SELECT ud.uti_id, ud.doc_id, COUNT(*) nb, MAX(ud.maj) lastDate, MIN(ud.maj) firstDate, udMe.maj myDate
FROM flux_utidoc ud
inner join flux_utidoc udMe on udMe.doc_id = ud.doc_id and udMe.uti_id = 1
GROUP BY ud.doc_id
) ptp
order by TimeSpace desc
