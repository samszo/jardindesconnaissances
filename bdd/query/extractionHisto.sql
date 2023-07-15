/*
select sum(eT.nb)/count(*) Moyenne
from(
*/
SELECT 
 -- d.pubDate temps,
 -- DATE_FORMAT(d.pubDate, "%d-%c-%y %H h") temps,
  DATE_FORMAT(d.pubDate, "%d-%c-%y") temps,
                          count(*) nb
    FROM flux_doc d
   WHERE d.tronc = ""
GROUP BY temps
ORDER BY temps
-- ) eT
