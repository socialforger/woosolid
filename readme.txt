=== WooSolid ===
Contributors: Socialforger
Tags: woocommerce, charitable, donations, ets, gas, checkout, fiscal data, pickup, importer
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

WooSolid integra WooCommerce e Charitable per gestire fee solidali, donazioni dirette, flussi ETS/GAS, punti di ritiro e riepiloghi fiscali nominativi.

== Description ==

WooSolid è un plugin progettato per ETS, GAS, cooperative e piattaforme civiche che necessitano di:

- **fee solidali** collegate ai prodotti WooCommerce  
- **donazioni dirette** (nominative o anonime) senza acquisto prodotti  
- **raccolta dati fiscale ETS** (persona fisica → rappresentante legale → persona giuridica)  
- **integrazione completa con Charitable**  
- **riepilogo fiscale annuale** inviabile all’utente  
- **punti di ritiro** integrati come metodo di spedizione  
- **importazione CSV** di prodotti solidali, campagne e pickup  
- **wizard iniziale** per configurazione ETS e logistica  

WooSolid è 100% open‑source, senza dipendenze esterne, senza servizi terzi, senza lock‑in.

### Funzionalità principali

**1. Fee solidali da prodotti WooCommerce**  
Ogni prodotto può generare una fee solidale collegata a una campagna Charitable.  
Modalità fee:  
- importo fisso  
- percentuale sul prezzo  

**2. Donazioni dirette (senza prodotti)**  
Dall’area utente:  
- donazione nominativa  
- donazione anonima  
- collegamento a qualsiasi campagna Charitable  

**3. Flusso fiscale ETS/GAS**  
Durante il checkout:  
- raccolta dati persona fisica  
- domanda: “Sei il rappresentante legale di una organizzazione?”  
- se SÌ → raccolta dati persona giuridica con precompilazione automatica  

**4. Riepilogo fiscale annuale**  
L’utente può richiedere via email il riepilogo delle donazioni nominative.

**5. Punti di ritiro**  
Metodo di spedizione WooCommerce dedicato:  
- punto di ritiro  
- data  
- orario  

**6. Importer CSV**  
Importazione da file CSV:  
- prodotti solidali  
- campagne Charitable  
- punti di ritiro  

**7. Wizard iniziale**  
Alla prima attivazione:  
- dati ETS  
- email ETS  
- logistica (spedizione/pickup)  

---

== Installation ==

1. Carica la cartella `woosolid` nella directory `/wp-content/plugins/`
2. Attiva il plugin da **Plugin → Aggiungi nuovo**
3. Alla prima attivazione verrai reindirizzato al **Wizard WooSolid**
4. Completa:
   - dati ETS  
   - email ETS  
   - impostazioni logistica  
5. Configura i prodotti solidali tramite il **metabox WooSolid** nella pagina prodotto
6. (Opzionale) Importa prodotti/campagne/pickup tramite **WooSolid → Importa**

---

== Frequently Asked Questions ==

= WooSolid richiede plugin aggiuntivi? =  
Sì:  
- WooCommerce  
- Charitable  

Nessun altro plugin è richiesto.

= Posso usare WooSolid per donazioni senza prodotti? =  
Sì.  
WooSolid aggiunge un endpoint “Fai una donazione” nell’area utente WooCommerce.

= Le fee solidali sono obbligatorie? =  
No.  
Se un prodotto non è collegato a una campagna Charitable, non genera fee.

= Le donazioni dirette sono anonime? =  
Possono essere:  
- anonime  
- nominative  

= WooSolid gestisce persona fisica e persona giuridica? =  
Sì.  
Il flusso è:  
1. dati persona fisica  
2. domanda rappresentante legale  
3. se SÌ → dati persona giuridica  

= Posso esportare o importare prodotti solidali? =  
Sì, tramite il modulo **WooSolid Importer**.

---

== Screenshots ==

1. Metabox prodotto con fee solidale  
2. Checkout con flusso PF/PG  
3. Endpoint “Le mie donazioni”  
4. Endpoint “Fai una donazione”  
5. Wizard iniziale ETS  
6. Importer CSV  

---

== Changelog ==

= 1.0.0 =
* Prima release stabile
* Fee solidali da prodotti WooCommerce
* Donazioni dirette nominative/anonime
* Flusso fiscale ETS/GAS (PF → PG)
* Riepilogo fiscale annuale via email
* Metodo di spedizione “Punto di ritiro”
* Importer CSV (prodotti, campagne, pickup)
* Wizard iniziale
* Metabox prodotto
* Integrazione completa con Charitable

---

== Upgrade Notice ==

= 1.0.0 =
Prima versione stabile del plugin WooSolid.
