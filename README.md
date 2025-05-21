
# Refactor of Inpatient Billing System — Java Swing Legacy Project

## 📌 Overview

This project is a real-world refactor of a legacy Java-based hospital billing system. It focuses on fixing critical issues related to duplicated procedure names in inpatient billing, enhancing accuracy and transparency in financial reports.

## 🧩 Problem

In the legacy billing system (`DlgBilingRanap.java`), procedure names were used as the sole identifier in SQL queries and report rendering. This caused:

- Duplicate procedure names with different billing codes to be treated as the same.
- Incorrect or missing doctor/facility fee splits.
- Ambiguity in printed billing reports for both hospital staff and patients.

## 🎯 Solution

- Modified `prosesCariTindakan()` method to use `procedure_code + procedure_name` as a combined unique identifier.
- Refactored the SQL queries in the `TabDrPr.addRow()` logic to include full identifiers.
- Created a new report file `LaporanBiayaPasien.java` (derived from `LaporanBilling2.java`) to reflect the updated data structure.
- Ensured the output now includes accurate breakdowns: doctor fee, assistant fee, facility share, and more.

## 📊 Output Comparison

### Before (Legacy Report)

| Procedure Name | Cost | Quantity |
|----------------|------|----------|
| EKG            | 85k  | 1        |

### After (Refactored Report)

| Code - Procedure Name | Cost | Facility Share | Doctor Fee | Assistant Fee | Quantity |
|------------------------|------|----------------|------------|----------------|----------|
| EKG001 - EKG           | 85k  | 20k            | 40k        | 25k            | 1        |

## 🧠 Lessons Learned

- Never rely solely on display names for identity in billing systems.
- Modularizing UI logic improves maintainability and future-proofing.
- Refactoring legacy systems requires not just code changes, but also schema-awareness and understanding of real-world workflows.

## 🗃️ File Structure

```
/before/         # Original legacy Java class
/after/          # Refactored Java version with improvements
/report/         # Screenshot of report before/after
README.md        # This documentation
```

## 🔗 Related Files

- `DlgBilingRanapBefore.java`
- `DlgBilingRanapAfter.java`
- `LaporanBilling2.java`
- `LaporanBiayaPasien.java`

> ⚠️ Note: Patient names and IDs in screenshots have been anonymized to protect privacy.

---

**Created and maintained by [Mihaaq Systems](https://github.com/mihaaq)**  
*Refactor Engineer • Java Modular Specialist • Worldbuilder*
