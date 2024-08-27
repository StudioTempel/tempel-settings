import fs from "fs";
import archiver from "archiver";

const inputGlobArray = [
  "**/*.php",
  "admin/**/*",
  "helpers/**/*",
  "includes/**/*",
  "vendor/**/*",
  "public/**/*",
  "dist/**/*",
  "languages/**/*",
  "*.md",
  "LICENSE",
];

const filename = `tempel-settings.zip`;

const output = fs.createWriteStream("build/" + filename);
const archive = archiver("zip", {
  zlib: { level: 9 },
});

output.on("close", function () {
  console.log(archive.pointer() + " total bytes");
  console.log(
    "archiver has been finalized and the output file descriptor has closed.",
  );
});

output.on("end", function () {
  console.log("Data has been drained");
});

archive.on("warning", function (err) {
  if (err.code === "ENOENT") {
    console.log(err);
  } else {
    throw err;
  }
});

archive.on("error", function (err) {
  throw err;
});

archive.pipe(output);

archive.directory("dist/", "dist");
archive.directory("includes/", "includes");
archive.directory("vendor/", "vendor");
archive.directory("languages/", "languages");
archive.directory("src/", "src");

archive.file("tempel.php", { name: "tempel.php" });
archive.file("README.md", { name: "README.md" });

archive.finalize();
