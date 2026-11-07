/*
 * Perilaku UI sisi klien untuk SIK (versi Laravel). Semua aturan bisnis tetap divalidasi ulang di server;
 * skrip ini hanya kenyamanan: baris dinamis, preview foto, toggle catatan wajib, notifikasi, dan modal master.
 */
(() => {
    const one = (selector, parent = document) => parent.querySelector(selector);
    const all = (selector, parent = document) => [...parent.querySelectorAll(selector)];
    const csrf = () => one('meta[name="csrf-token"]')?.content || "";

    // Validasi server: fokus ke field pertama yang salah
    // Hanya saat halaman dimuat dengan error (redirect setelah validasi gagal).
    // Field yang sudah diperbaiki user kembali bersih, supaya pesan tidak menempel.
    const firstInvalid = one(".is-invalid");
    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: "smooth", block: "center" });
        if (typeof firstInvalid.focus === "function") firstInvalid.focus({ preventScroll: true });
    }
    all("form[data-dynamic-form]").forEach((form) => {
        const clearInvalid = (event) => event.target.classList?.remove("is-invalid");
        form.addEventListener("input", clearInvalid);
        form.addEventListener("change", clearInvalid);
    });

    // Peringatan templat: hanya dirender di form pemohon, bukan form koreksi Admin.
    // Saat tampil, tombol Kirim dinonaktifkan (Simpan draf tetap aktif); pagar sebenarnya ada di server saat Kirim.
    const templateWarning = one("#templateWarning");
    if (templateWarning) {
        const templateForm = templateWarning.closest("form") || document;
        const areaSelect = one('[name="area"]', templateForm);
        const workTypeSelect = one('[name="workType"]', templateForm);
        const submitButton = one('button[type="submit"][value="submit"]', templateForm);
        const templateCombos = new Set(JSON.parse(templateWarning.dataset.combos || "[]"));
        const updateTemplateWarning = () => {
            const area = areaSelect?.value;
            const workType = workTypeSelect?.value;
            const blocked = Boolean(area && workType && !templateCombos.has(`${area}:${workType}`));
            templateWarning.hidden = !blocked;
            if (submitButton) submitButton.disabled = blocked;
        };
        areaSelect?.addEventListener("change", updateTemplateWarning);
        workTypeSelect?.addEventListener("change", updateTemplateWarning);
        // Pengaman bila submit dipicu tanpa tombol Kirim (mis. Enter): hanya
        // blokir aksi Kirim, bukan Simpan draf.
        templateForm.addEventListener?.("submit", (event) => {
            if (event.submitter?.value === "submit" && !templateWarning.hidden) {
                event.preventDefault();
                templateWarning.scrollIntoView({ behavior: "smooth", block: "center" });
            }
        });
        updateTemplateWarning();
    }

    // Sidebar (mobile)
    const sidebarToggle = one("#sidebarToggle");
    if (sidebarToggle) {
        sidebarToggle.addEventListener("click", () => {
            document.body.classList.toggle("sidebar-open");
            sidebarToggle.setAttribute("aria-expanded", String(document.body.classList.contains("sidebar-open")));
        });
        document.addEventListener("click", (event) => {
            const sidebar = one("#sikSidebar");
            if (document.body.classList.contains("sidebar-open") && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                document.body.classList.remove("sidebar-open");
            }
        });
    }

    // Form pengajuan: baris personel / barang
    const updateCounts = (form) => {
        all("[data-count-for]", form).forEach((badge) => {
            const type = badge.dataset.countFor;
            const rows = all(`[data-rows="${type}"] > tr`, form).length;
            badge.textContent = `${rows} ${badge.dataset.unit}`;
        });
    };

    all("form[data-dynamic-form]").forEach((form) => {
        updateCounts(form);

        form.addEventListener("click", (event) => {
            const add = event.target.closest("[data-add-row]");
            if (add) {
                const type = add.dataset.addRow;
                const template = one(`#tpl-${type}`);
                const body = one(`[data-rows="${type}"]`, form);
                body.append(template.content.cloneNode(true));
                updateCounts(form);
                const last = body.lastElementChild;
                one("input:not([type=hidden])", last)?.focus();
                return;
            }
            const remove = event.target.closest(".remove-row");
            if (remove) {
                const row = remove.closest("tr");
                const body = row.parentElement;
                if (body.children.length === 1) {
                    window.alert("Minimal harus ada satu baris pada tabel.");
                    return;
                }
                const preview = one(".photo-preview", row);
                if (preview?.src.startsWith("blob:")) URL.revokeObjectURL(preview.src);
                row.remove();
                updateCounts(form);
                return;
            }
            // Kirim sengaja tidak dicegat reportValidity: gate itu menyembunyikan error server di balik tanda *.
            // Submit diteruskan ke server, lalu halaman kembali dengan $errors lengkap.
        });

        form.addEventListener("input", (event) => {
            if (event.target.matches('[name="startDate"], [name="endDate"]')) one('[name="endDate"]', form)?.setCustomValidity("");
        });

        form.addEventListener("change", (event) => {
            const input = event.target;
            const row = input.closest(".equipment-row");
            if (!row) return;
            const movement = one('[name="barang_pergerakan[]"]', row);
            if (input.matches("[data-consumable-toggle]")) {
                // barang_consumable[] selalu satu nilai per baris ("1"/"0"); habis pakai => arah dikunci IN.
                one('[name="barang_consumable[]"]', row).value = input.checked ? "1" : "0";
                if (input.checked) movement.value = "IN";
            } else if (input.matches('[name="barang_pergerakan[]"]')) {
                if (one("[data-consumable-toggle]", row).checked) input.value = "IN";
            } else if (input.matches(".photo-input") && input.files[0]) {
                const file = input.files[0];
                if (!file.type.startsWith("image/")) {
                    input.value = "";
                    window.alert("Pilih berkas gambar untuk foto peralatan.");
                    return;
                }
                const preview = one(".photo-preview", row);
                if (preview.src.startsWith("blob:")) URL.revokeObjectURL(preview.src);
                preview.src = URL.createObjectURL(file);
                preview.hidden = false;
                one(".photo-trigger", row).hidden = true;
            }
        });
    });

    // Stepper: tandai bagian form yang sedang terlihat.
    const steps = all("[data-form-step]");
    if (steps.length && "IntersectionObserver" in window) {
        const observer = new IntersectionObserver((entries) => {
            const visible = entries.filter((entry) => entry.isIntersecting).sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];
            if (visible) steps.forEach((step) => step.classList.toggle("current", step.dataset.formStep === visible.target.id));
        }, { rootMargin: "-18% 0px -62% 0px", threshold: [0, 0.2, 0.5] });
        steps.forEach((step) => {
            const section = document.getElementById(step.dataset.formStep);
            if (section) observer.observe(section);
            step.addEventListener("click", (event) => {
                event.preventDefault();
                section?.scrollIntoView({ behavior: "smooth", block: "start" });
            });
        });
    }

    // Keputusan Approver
    const decisionForm = one("[data-decision-form]");
    if (decisionForm) {
        const comment = one("#decisionComment", decisionForm);
        const requirement = one("[data-comment-requirement]", decisionForm);
        const button = one("[data-confirm-button]", decisionForm);
        const labels = {
            approve: 'Konfirmasi persetujuan <i class="fa-solid fa-arrow-right ms-2"></i>',
            return: 'Kembalikan ke admin <i class="fa-solid fa-rotate-left ms-2"></i>',
            reject: 'Tolak pengajuan <i class="fa-solid fa-ban ms-2"></i>',
        };
        const refresh = () => {
            const decision = one('input[name="decision"]:checked', decisionForm).value;
            const needsNote = decision !== "approve";
            comment.required = needsNote;
            requirement.textContent = needsNote ? "(wajib diisi)" : "(opsional untuk persetujuan)";
            button.innerHTML = labels[decision];
            button.className = decision === "reject" ? "btn btn-danger" : "btn btn-primary";
        };
        all('input[name="decision"]', decisionForm).forEach((radio) => radio.addEventListener("change", refresh));
        comment.addEventListener("input", () => comment.classList.remove("is-invalid"));
        decisionForm.addEventListener("submit", (event) => {
            if (comment.required && !comment.value.trim()) {
                event.preventDefault();
                comment.classList.add("is-invalid");
                comment.focus();
            }
        });
        refresh();
    }

    // Verifikasi: revisi wajib catatan
    const verificationForm = one("[data-verification-form]");
    if (verificationForm) {
        const note = one("#verificationNote", verificationForm);
        verificationForm.addEventListener("submit", (event) => {
            if (event.submitter?.value === "revision" && !note.value.trim()) {
                event.preventDefault();
                note.classList.add("is-invalid");
                note.focus();
            }
        });
        note.addEventListener("input", () => note.classList.remove("is-invalid"));
    }

    // Koreksi: tutup pengajuan hanya bila ditandai
    const closeForm = one("[data-close-form]");
    if (closeForm) {
        const toggle = one("[data-close-toggle]", closeForm);
        const button = one("[data-close-button]", closeForm);
        toggle.addEventListener("change", () => { button.disabled = !toggle.checked; });
        closeForm.addEventListener("submit", (event) => {
            if (!window.confirm("Tutup pengajuan ini? Pemohon harus mengajukan ulang dari awal.")) event.preventDefault();
        });
    }

    // Penerbitan SIK: pratinjau & unduh memakai lembar SIK yang sama
    // Nomor/tanggal isian hanya dipakai untuk URL pratinjau & PDF (data pratinjau, tidak disimpan).
    const permitNumber = one("#issuedPermitNumber");
    const issueDate = one("#issueDate");
    const permitTriggers = permitNumber ? [...document.querySelectorAll("[data-permit-src], [data-issued-pdf]")] : [];
    if (permitNumber && permitTriggers.length) {
        permitTriggers.forEach((el) => {
            el.dataset.baseSrc = el.getAttribute("data-permit-src") || "";
            el.dataset.basePdf = el.getAttribute("data-permit-pdf") || el.getAttribute("href") || "";
        });

        const sync = () => {
            const query = new URLSearchParams();
            if (permitNumber.value.trim()) query.set("number", permitNumber.value.trim());
            if (issueDate && issueDate.value) query.set("date", issueDate.value);

            permitTriggers.forEach((el) => {
                const suffix = query.toString() ? `?${query.toString()}` : "";
                if (el.dataset.baseSrc) el.setAttribute("data-permit-src", `${el.dataset.baseSrc.split("?")[0]}?embed=1${suffix ? `&${query.toString()}` : ""}`);
                if (el.dataset.basePdf) {
                    const url = `${el.dataset.basePdf.split("?")[0]}${suffix}`;
                    if (el.tagName === "A") {
                        el.setAttribute("href", url);
                    } else {
                        el.setAttribute("data-permit-pdf", url);
                    }
                }
            });
        };

        permitNumber.addEventListener("input", sync);
        if (issueDate) issueDate.addEventListener("input", sync);
        sync();
    }

    // Notifikasi
    const bell = one("#notificationToggle");
    if (bell) {
        const menu = one("#notificationMenu");
        const indicator = one("#notificationIndicator");
        const render = (data) => {
            indicator.hidden = data.unread === 0;
            menu.replaceChildren();
            const header = document.createElement("div");
            header.className = "notification-menu-header";
            const title = document.createElement("strong");
            title.textContent = "Notifikasi";
            const mark = document.createElement("button");
            mark.type = "button";
            mark.className = "btn btn-sm btn-link";
            mark.textContent = "Tandai semua dibaca";
            mark.addEventListener("click", async (event) => {
                event.stopPropagation();
                await fetch(bell.dataset.readUrl, { method: "POST", headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" } });
                load();
            });
            header.append(title, mark);
            menu.append(header);
            if (!data.items.length) {
                const empty = document.createElement("p");
                empty.className = "dropdown-item-text mb-0";
                empty.textContent = "Tidak ada notifikasi baru.";
                menu.append(empty);
                return;
            }
            data.items.forEach((item) => {
                const link = document.createElement("a");
                link.className = `dropdown-item notification-item${item.unread ? " notification-unread" : ""}`;
                link.href = item.url;
                const text = document.createElement("span");
                text.textContent = item.text;
                const time = document.createElement("small");
                time.textContent = item.time;
                link.append(text, time);
                menu.append(link);
            });
        };
        const load = async () => {
            try {
                const response = await fetch(bell.dataset.url, { headers: { Accept: "application/json" } });
                if (response.ok) render(await response.json());
            } catch (error) { /* jaringan gagal: biarkan kosong */ }
        };
        load();
        window.setInterval(load, 60000);
    }

    // Modal master data (Area / Jenis pekerjaan / Departemen)
    const showModal = (selector) => bootstrap.Modal.getOrCreateInstance(one(selector)).show();
    const masterForm = one("#masterForm");
    if (masterForm) {
        const open = (button, editing) => {
            masterForm.action = button.dataset.action;
            one("#masterMethod").disabled = !editing;
            one("#masterModalLabel").textContent = button.dataset.title;
            one("#masterName").value = editing ? button.dataset.name : "";
            one("#masterCode").value = editing ? button.dataset.code : "";
            showModal("#masterModal");
        };
        document.addEventListener("click", (event) => {
            const add = event.target.closest("[data-master-add]");
            if (add) return open(add, false);
            const edit = event.target.closest("[data-master-edit]");
            if (edit) open(edit, true);
        });
    }

    // Modal pengguna
    const userForm = one("#userForm");
    if (userForm) {
        const open = (button, editing) => {
            userForm.action = button.dataset.action;
            one("#userMethod").disabled = !editing;
            one("#userModalLabel").textContent = editing ? "Ubah pengguna" : "Tambah pengguna";
            one("#userName").value = editing ? button.dataset.name : "";
            one("#userUsername").value = editing ? button.dataset.username : "";
            const password = one("#userPassword");
            password.value = "";
            password.required = !editing;
            password.placeholder = editing ? "Kosongkan untuk mempertahankan kata sandi" : "";
            one("#userRole").value = editing ? button.dataset.role : "";
            // Penugasan unit: centang semua unit yang tersimpan (data-departments = array ID).
            const unitIds = editing ? JSON.parse(button.dataset.departments || "[]").map(String) : [];
            all('[name="departments[]"]', userForm).forEach((box) => { box.checked = unitIds.includes(box.value); });
            showModal("#userModal");
        };
        document.addEventListener("click", (event) => {
            const add = event.target.closest("[data-user-add]");
            if (add) return open(add, false);
            const edit = event.target.closest("[data-user-edit]");
            if (edit) open(edit, true);
        });
    }

    // Konfirmasi tindakan destruktif (hapus pengguna/peran/master data)
    document.addEventListener("submit", (event) => {
        const form = event.target.closest("form[data-confirm]");
        if (form && !window.confirm(form.dataset.confirm)) event.preventDefault();
    });

    // Modal templat alur (maks. 8 tahap)
    const workflowForm = one("#workflowForm");
    if (workflowForm) {
        const container = one("#workflowSteps");
        const addButton = one("#addWorkflowStep");
        const renumber = () => {
            const rows = all(".workflow-step-row", container);
            rows.forEach((row, index) => {
                one(".workflow-step-number", row).textContent = `Tahap ${index + 1}`;
                one(".workflow-title-input", row).name = `steps[${index}][title]`;
                one(".workflow-account-select", row).name = `steps[${index}][approver]`;
                one(".remove-workflow-step", row).disabled = rows.length === 1;
            });
            addButton.disabled = rows.length >= 8;
        };
        const addStep = (title = "", approver = "") => {
            if (container.children.length >= 8) return;
            const row = one("#tpl-workflow-step").content.cloneNode(true);
            one(".workflow-title-input", row).value = title;
            one(".workflow-account-select", row).value = String(approver);
            container.append(row);
            renumber();
        };
        const open = (button, editing) => {
            workflowForm.action = button.dataset.action;
            one("#workflowMethod").disabled = !editing;
            one("#workflowModalLabel").textContent = editing ? "Ubah templat alur kerja" : "Tambah templat alur kerja";
            one("#workflowArea").value = editing ? button.dataset.area : "";
            one("#workflowWorkType").value = editing ? button.dataset.workType : "";
            container.replaceChildren();
            const steps = editing ? JSON.parse(button.dataset.steps || "[]") : [];
            if (steps.length) steps.forEach((step) => addStep(step.title, step.approver)); else addStep();
            showModal("#workflowModal");
        };
        addButton.addEventListener("click", () => addStep());
        container.addEventListener("click", (event) => {
            if (event.target.closest(".remove-workflow-step")) {
                event.target.closest(".workflow-step-row").remove();
                renumber();
            }
        });
        document.addEventListener("click", (event) => {
            const add = event.target.closest("[data-workflow-add]");
            if (add) return open(add, false);
            const edit = event.target.closest("[data-workflow-edit]");
            if (edit) open(edit, true);
        });
    }

    // Modal pratinjau SIK: iframe pratinjau + unduh PDF langsung (fetch → blob)
    const permitModal = one("#permitModal");
    if (permitModal) {
        const frame = one(".permit-frame", permitModal);
        const downloadButton = one("[data-permit-download]", permitModal);
        const downloadLabel = one("[data-permit-download-label]", permitModal);
        const status = one("[data-permit-status]", permitModal);
        const idleLabel = downloadLabel?.textContent?.trim() || "Unduh PDF";
        let pdfUrl = "";
        let busy = false;

        const setStatus = (message, isError = false) => {
            if (!status) return;
            status.textContent = message;
            status.classList.toggle("is-error", Boolean(message) && isError);
            status.classList.toggle("is-ok", Boolean(message) && !isError);
        };

        // Ambil nama berkas dari header Content-Disposition (fallback: SIK.pdf).
        const filenameFrom = (disposition) => {
            if (!disposition) return "";
            const utf8 = /filename\*=UTF-8''([^;]+)/i.exec(disposition);
            if (utf8) { try { return decodeURIComponent(utf8[1].trim()); } catch (error) { /* abaikan */ } }
            const plain = /filename="?([^";]+)"?/i.exec(disposition);
            return plain ? plain[1].trim() : "";
        };

        permitModal.addEventListener("show.bs.modal", (event) => {
            const trigger = event.relatedTarget;
            const preview = trigger?.getAttribute("data-permit-src") || "";
            // Utamakan atribut eksplisit; bila halaman lama (cache) belum punya, turunkan dari URL pratinjau.
            pdfUrl = trigger?.getAttribute("data-permit-pdf") || (preview ? preview.split("?")[0] + "/pdf" : "");
            if (preview) frame.setAttribute("src", preview);
            if (downloadButton) downloadButton.disabled = !pdfUrl;
            setStatus("");
        });

        // Kosongkan iframe saat ditutup agar dokumen tidak tetap termuat di latar belakang.
        permitModal.addEventListener("hidden.bs.modal", () => {
            frame.setAttribute("src", "about:blank");
        });

        downloadButton?.addEventListener("click", async () => {
            if (busy || !pdfUrl) return;
            busy = true;
            downloadButton.disabled = true;
            if (downloadLabel) downloadLabel.textContent = "Menyiapkan…";
            setStatus("");

            try {
                const response = await fetch(pdfUrl, {
                    credentials: "same-origin",
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                });
                if (!response.ok) throw new Error("HTTP " + response.status);

                const blob = await response.blob();
                if (!blob.size) throw new Error("Berkas kosong");
                if (blob.type && !blob.type.toLowerCase().includes("pdf")) throw new Error("Bukan PDF");

                const filename = filenameFrom(response.headers.get("Content-Disposition")) || "SIK.pdf";
                const objectUrl = URL.createObjectURL(blob);
                const link = document.createElement("a");
                link.href = objectUrl;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(objectUrl), 5000);
                setStatus("PDF diunduh: " + filename);
            } catch (error) {
                const kode = error && error.message ? error.message : "";
                if (kode.includes("401") || kode.includes("419")) {
                    setStatus("Sesi Anda berakhir. Muat ulang halaman lalu coba lagi.", true);
                } else if (kode.includes("403")) {
                    setStatus("Anda tidak berhak mengunduh SIK ini.", true);
                } else if (kode.includes("404")) {
                    setStatus("Dokumen SIK tidak ditemukan untuk pengajuan ini.", true);
                } else {
                    setStatus("Gagal mengunduh PDF. Silakan coba lagi.", true);
                }
            } finally {
                busy = false;
                downloadButton.disabled = !pdfUrl;
                if (downloadLabel) downloadLabel.textContent = idleLabel;
            }
        });
    }
})();
