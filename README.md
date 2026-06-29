# study_notes

Webエンジニア向けの勉強会・学習用教材を topic ごとにまとめたリポジトリです。

## 目次

### C言語入門 — [`c_lang/`](c_lang/)

Webエンジニアのための C言語ハンズオン勉強会の資料です。

- [`001_make.markdown`](c_lang/001_make.markdown) — C言語のビルド(make)について
- [`002_debug.markdown`](c_lang/002_debug.markdown) — C言語プログラムのデバッグ
- [`day0.markdown`](c_lang/day0.markdown) 〜 [`day4.markdown`](c_lang/day4.markdown) — ハンズオン #0〜#4
- [`day1_answer.markdown`](c_lang/day1_answer.markdown) — day1 の解答例

### チューリングマシン (PHP) — [`turing_machine/`](turing_machine/)

チューリングマシンを PHP で実装する教材です。単項加算 (`111+11` → `11111`) を題材にしています。

- [`001_turing_machine.markdown`](turing_machine/001_turing_machine.markdown) — 解説資料
- [`turing_machine.php`](turing_machine/turing_machine.php) — TM本体 + 単項加算マシン
- [`test_turing_machine.php`](turing_machine/test_turing_machine.php) — 検証用スクリプト
- [`plan.md`](turing_machine/plan.md) — 教材の設計メモ

```bash
php turing_machine/turing_machine.php "111+11"
```
